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
        {--dry-run : Preview without saving}';

    protected $description = 'Enrich competitions by scraping detail pages from infolomba.id for category, description, links';

    private const BASE_URL = 'https://infolomba.id';

    public function handle(InfoLombaScraperService $scraper): int
    {
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        // Step 1: Collect detail page URLs using the existing scraper service
        $this->info('📡 Collecting detail page URLs...');

        $urlItems = $scraper->collectUrls($limit, function (int $collected, int $total) {
            $this->output->write("\r  Collected: {$collected} / {$total}");
        });

        $this->newLine();
        $this->info('  Found ' . count($urlItems) . ' detail URLs');
        $this->newLine();

        if (empty($urlItems)) {
            $this->error('No URLs found.');
            return self::FAILURE;
        }

        // Step 2: Process each detail page
        $enriched = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar(count($urlItems));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $progressBar->setMessage('Starting...');
        $progressBar->start();

        foreach ($urlItems as $item) {
            try {
                $detail = $this->fetchDetailPage($item['url']);

                if (empty($detail) || empty($detail['title'])) {
                    $skipped++;
                    $progressBar->setMessage("Empty page: " . substr($item['url'], -30));
                    $progressBar->advance();
                    usleep(400_000);
                    continue;
                }

                // Match to DB by title (case-insensitive)
                $competition = Competition::whereRaw(
                    'LOWER(title) = ?',
                    [mb_strtolower(trim($detail['title']))]
                )->first();

                if (!$competition) {
                    $skipped++;
                    $progressBar->setMessage("No DB match: " . substr($detail['title'], 0, 35));
                    $progressBar->advance();
                    usleep(400_000);
                    continue;
                }

                // Build update data
                $updateData = array_filter([
                    'category' => (!empty($detail['category']) && $detail['category'] !== '~Lainnya')
                        ? $detail['category'] : null,
                    'description' => $detail['description'] ?? null,
                    'external_registration_url' => $detail['registration_url'] ?? null,
                    'official_website' => $detail['guidebook_url'] ?? null,
                    'social_media' => $detail['social_media'] ?? null,
                    'source_url' => $item['url'],
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
                ['Detail pages fetched', count($urlItems)],
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
        $response = Http::withoutVerifying()->timeout(30)->get($url);

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
                $category = preg_replace('/\s+/', ' ', $category);
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
                $text = preg_replace('/<br\s*\/?>/i', "\n", $descHtml);
                $text = preg_replace('/<\/p>/i', "\n\n", $text);
                $text = strip_tags($text);
                $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
                $text = preg_replace('/[ \t]+/', ' ', $text);
                $text = preg_replace('/\n{3,}/', "\n\n", $text);
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
                    if ($href && !str_contains($href, 'void') && $href !== '#') {
                        $data['registration_url'] = $href;
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
                    if ($href && !str_contains($href, 'void') && $href !== '#') {
                        $data['guidebook_url'] = $href;
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
}
