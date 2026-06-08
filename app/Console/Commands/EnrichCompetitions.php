<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition;
use App\Services\InfoLombaScraperService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class EnrichCompetitions extends Command
{
    protected $signature = 'siperlo:enrich
        {--limit=500 : Max number of detail pages to fetch}
        {--dry-run : Preview without saving}
        {--source=db : Source of URLs (db or web)}';

    protected $description = 'Enrich competitions by scraping detail pages from infolomba.id for category, description, links';

    private const BASE_URL = 'https://infolomba.id';

    public function handle(InfoLombaScraperService $scraper): int
    {
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');
        $source = $this->option('source');

        $itemsToProcess = [];

        if ($source === 'db') {
            $this->info('📡 Fetching competitions from database to enrich...');
            $dbCompetitions = Competition::where('is_scraped', false)
                ->whereNotNull('source_url')
                ->limit($limit)
                ->get();

            $this->info('  Found ' . $dbCompetitions->count() . ' competitions needing enrichment');
            foreach ($dbCompetitions as $comp) {
                $itemsToProcess[] = [
                    'url' => $comp->source_url,
                    'model' => $comp,
                ];
            }
        } else {
            $this->info('📡 Collecting detail page URLs from infolomba.id...');
            $urlItems = $scraper->collectUrls($limit, function (int $collected, int $total) {
                $this->output->write("\r  Collected: {$collected} / {$total}");
            });

            $this->newLine();
            $this->info('  Found ' . count($urlItems) . ' detail URLs');

            foreach ($urlItems as $item) {
                $itemsToProcess[] = [
                    'url' => $item['url'],
                    'model' => null,
                ];
            }
        }

        $this->newLine();

        if (empty($itemsToProcess)) {
            $this->error('No competitions to enrich.');
            return self::SUCCESS;
        }

        // Step 2: Process each detail page
        $enriched = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar(count($itemsToProcess));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $progressBar->setMessage('Starting...');
        $progressBar->start();

        foreach ($itemsToProcess as $item) {
            try {
                $sourceUrl = $this->normalizeInfoLombaUrl($item['url']);

                if ($sourceUrl === null) {
                    $skipped++;
                    $progressBar->setMessage('Skipped invalid source URL');
                    $progressBar->advance();
                    continue;
                }

                $detail = $this->fetchDetailPage($sourceUrl);

                if (empty($detail) || empty($detail['title'])) {
                    $skipped++;
                    $progressBar->setMessage("Empty page: " . substr($item['url'], -30));
                    $progressBar->advance();
                    usleep(400_000);
                    continue;
                }

                $competition = $item['model'];
                if (!$competition) {
                    // Match to DB by title (case-insensitive)
                    $competition = Competition::whereRaw(
                        'LOWER(title) = ?',
                        [mb_strtolower(trim($detail['title']))]
                    )->first();
                }

                if (!$competition) {
                    $skipped++;
                    $progressBar->setMessage("No DB match: " . substr($detail['title'], 0, 35));
                    $progressBar->advance();
                    usleep(400_000);
                    continue;
                }

                // Build update data with truncations to prevent right truncation errors
                $updateData = array_filter([
                    'category' => (!empty($detail['category']) && $detail['category'] !== '~Lainnya')
                        ? mb_substr($detail['category'], 0, 255) : null,
                    'description' => $detail['description'] ?? null,
                    'external_registration_url' => isset($detail['registration_url'])
                        ? mb_substr($detail['registration_url'], 0, 255) : null,
                    'official_website' => isset($detail['guidebook_url'])
                        ? mb_substr($detail['guidebook_url'], 0, 255) : null,
                    'social_media' => isset($detail['social_media'])
                        ? mb_substr($detail['social_media'], 0, 255) : null,
                    'source_url' => mb_substr($sourceUrl, 0, 255),
                    'is_scraped' => true,
                ], fn ($v) => $v !== null && $v !== '');

                if (!empty($updateData)) {
                    if (!$dryRun) {
                        $competition->update($updateData);
                    }
                    $enriched++;
                    $cat = $updateData['category'] ?? $competition->category;
                    $progressBar->setMessage("✅ [{$cat}] " . substr($detail['title'], 0, 30));
                } else {
                    $skipped++;
                }

            } catch (\Exception $e) {
                $errors++;
                $progressBar->setMessage("❌ " . substr($e->getMessage(), 0, 40));
            }

            $progressBar->advance();
            usleep(400_000); // Polite delay
        }

        $progressBar->setMessage('Done!');
        $progressBar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Competitions processed', count($itemsToProcess)],
                ['Enriched', $enriched],
                ['Skipped', $skipped],
                ['Errors', $errors],
            ]
        );

        if ($dryRun) {
            $this->warn('Dry run — no changes saved.');
        }

        // Show category distribution
        $this->newLine();
        $this->info('📊 Updated category distribution:');
        $cats = Competition::selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(15)
            ->get();
        foreach ($cats as $cat) {
            $this->line("  {$cat->category}: {$cat->total}");
        }

        $this->newLine();
        $this->info('🎉 Enrichment complete!');

        return self::SUCCESS;
    }

    /**
     * Fetch and parse a single detail page.
     *
     * @return array{title?: string, category?: string, description?: string, registration_url?: string, guidebook_url?: string, social_media?: string}
     */
    private function fetchDetailPage(string $url): array
    {
        $url = $this->normalizeInfoLombaUrl($url);

        if ($url === null) {
            return [];
        }

        $response = Http::timeout(30)->get($url);

        if ($response->failed()) {
            return [];
        }

        $crawler = new Crawler($response->body());
        $data = [];

        // Title from mobile view's .event-title (more reliable)
        try {
            $titleNode = $crawler->filter('.event-details-container.mobile .event-title');
            if ($titleNode->count() > 0) {
                $data['title'] = trim($titleNode->first()->text(''));
            } else {
                // Fallback: any .event-title in event-details
                $titleNode = $crawler->filter('.event-details-container .event-title');
                if ($titleNode->count() > 0) {
                    $data['title'] = trim($titleNode->first()->text(''));
                }
            }
        } catch (\Exception) {
        }

        // Category from .kategori-item
        try {
            $catNode = $crawler->filter('.kategori-item');
            if ($catNode->count() > 0) {
                $category = trim($catNode->first()->text(''));
                $category = preg_replace('/\s+/u', ' ', $category);
                if ($category) {
                    $data['category'] = $category;
                }
            }
        } catch (\Exception) {
        }

        // Description from .event-description-container
        try {
            $descNode = $crawler->filter('.event-description-container');
            if ($descNode->count() > 0) {
                $descHtml = $descNode->first()->html();
                // Clean invalid UTF-8 bytes first
                $descHtml = mb_convert_encoding($descHtml, 'UTF-8', 'UTF-8');

                $text = preg_replace('/<br\s*\/?>/iu', "\n", $descHtml) ?? $descHtml;
                $text = preg_replace('/<\/p>/iu', "\n\n", $text) ?? $text;
                $text = strip_tags($text);
                $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
                $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
                $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;
                $text = trim($text);
                if (mb_strlen($text) > 10) {
                    $data['description'] = $text;
                }
            }
        } catch (\Exception) {
        }

        // Registration URL from "Daftar Sekarang" button
        try {
            $crawler->filter('a.btn-primary')->each(function (Crawler $btn) use (&$data) {
                $text = mb_strtolower(trim($btn->text('')));
                if (str_contains($text, 'daftar')) {
                    $href = $btn->attr('href') ?? '';
                    if ($url = $this->normalizeExternalUrl($href)) {
                        $data['registration_url'] = $url;
                    }
                }
            });
        } catch (\Exception) {
        }

        // Guidebook URL from "Panduan Lomba" button
        try {
            $crawler->filter('a.btn-event-details')->each(function (Crawler $btn) use (&$data) {
                $text = mb_strtolower(trim($btn->text('')));
                if (str_contains($text, 'panduan')) {
                    $href = $btn->attr('href') ?? '';
                    if ($url = $this->normalizeExternalUrl($href)) {
                        $data['guidebook_url'] = $url;
                    }
                }
            });
        } catch (\Exception) {
        }

        // Social media from organizer profile
        try {
            $socials = [];
            $crawler->filter('.profile-event-details-container .other-details .item')
                ->each(function (Crawler $item) use (&$socials) {
                    $text = trim($item->text(''));
                    if (str_contains(mb_strtolower($text), 'instagram') ||
                        str_contains(mb_strtolower($text), 'sosial')) {
                        $socials[] = $text;
                    }
                });
            if (!empty($socials)) {
                $data['social_media'] = implode('; ', $socials);
            }
        } catch (\Exception) {
        }

        return $data;
    }

    /**
     * Normalize and allowlist detail URLs fetched by this command.
     */
    private function normalizeInfoLombaUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '' || $url === '#') {
            return null;
        }

        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = self::BASE_URL . '/' . ltrim($url, '/');
        }

        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower($parts['host'] ?? '');

        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        if (! in_array($host, ['infolomba.id', 'www.infolomba.id'], true)) {
            return null;
        }

        return $url;
    }

    /**
     * Keep only normal web links before saving scraped external URLs.
     */
    private function normalizeExternalUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '' || $url === '#' || str_contains(strtolower($url), 'void')) {
            return null;
        }

        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        } elseif (str_starts_with($url, '/')) {
            $url = self::BASE_URL . $url;
        }

        $scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?? '');

        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    }
}
