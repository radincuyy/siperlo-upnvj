<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class InfoLombaScraperService
{
    private const BASE_URL = 'https://infolomba.id';
    private const LOAD_EVENT_URL = 'https://infolomba.id/load-event';
    private const BATCH_SIZE = 10;

    /**
     * Infolomba category ID → label mapping (extracted from the site).
     *
     * @var array<int, string>
     */
    private const CATEGORY_MAP = [
        1  => 'Olimpiade',
        2  => 'Desain',
        3  => 'Karya Tulis Ilmiah',
        4  => 'Musik',
        5  => 'Olahraga',
        6  => 'Bisnis',
        7  => 'Sastra',
        8  => 'Agama',
        9  => 'IT',
        10 => 'Seminar',
        11 => 'Beasiswa',
        12 => 'Seni',
        13 => 'Pramuka',
        14 => 'Media Pembelajaran',
        15 => 'Rally Games',
        16 => 'Lainnya',
        17 => 'Try Out',
        18 => 'E-sport',
        19 => 'Fotografi',
        20 => 'Videografi/Film',
        21 => 'Challenge',
        22 => 'Esai',
        23 => 'Debat',
        24 => 'Pelatihan',
        25 => 'Infografis',
        26 => 'Pidato',
        27 => 'English',
        28 => 'Hukum',
        29 => 'Teknik',
        30 => 'Poster',
        31 => 'Artikel',
        32 => 'Akuntansi',
        33 => 'Pajak',
        34 => 'Keuangan',
        35 => 'Robot',
        36 => 'Ambassador',
        37 => 'Cerdas Cermat',
        38 => 'PMR',
        39 => 'Kesehatan',
        40 => 'Bahasa Asing',
        41 => 'Paper',
        42 => 'Mewarnai',
        43 => 'Permainan',
        44 => 'UI/UX',
        45 => 'Trading',
        46 => 'Stand Up Comedy',
        47 => 'Automotif',
        48 => 'Voice Over',
        49 => 'Podcast',
        50 => 'Dance/Tari',
        51 => 'News Anchor/Pembawa Berita',
        52 => 'Baris Berbaris',
        53 => 'MC/Protocol',
        54 => 'Menggambar/Drawing/Ilustrasi',
        55 => 'Giveaway',
        56 => 'Story Telling',
        57 => 'Fashion Show',
        58 => 'Statistika/Data',
    ];

    /**
     * Collect competition URLs from the listing page by calling
     * the load-event AJAX endpoint repeatedly.
     *
     * @param  int  $totalNeeded  Number of URLs to collect
     * @param  callable|null  $onBatch  Callback after each batch: fn(int $collected, int $total)
     * @return list<array{id: int, slug: string, url: string}>
     */
    public function collectUrls(int $totalNeeded = 500, ?callable $onBatch = null): array
    {
        $urls = [];

        // First, scrape the initial page (offset 0 = already rendered in the page HTML)
        $response = Http::timeout(30)->get(self::BASE_URL);
        if ($response->failed()) {
            throw new \RuntimeException('Failed to fetch infolomba.id homepage');
        }

        $crawler = new Crawler($response->body());
        $this->extractUrlsFromCrawler($crawler, $urls);

        if ($onBatch) {
            $onBatch(count($urls), $totalNeeded);
        }

        // Then, use the load-event API for more
        $offset = self::BATCH_SIZE;

        while (count($urls) < $totalNeeded) {
            $response = Http::timeout(30)
                ->asForm()
                ->post(self::LOAD_EVENT_URL, ['start' => $offset]);

            if ($response->failed()) {
                break;
            }

            $json = $response->json();

            if (empty($json['html']) || ($json['event_count'] ?? 0) === 0) {
                break;
            }

            $batchCrawler = new Crawler('<div>' . $json['html'] . '</div>');
            $this->extractUrlsFromCrawler($batchCrawler, $urls);

            if ($onBatch) {
                $onBatch(count($urls), $totalNeeded);
            }

            if (($json['event_count'] ?? 0) < self::BATCH_SIZE) {
                break; // No more events
            }

            $offset += self::BATCH_SIZE;

            // Polite delay to avoid hammering the server
            usleep(300_000); // 300ms
        }

        return array_slice($urls, 0, $totalNeeded);
    }

    /**
     * Extract event URLs from a DOM crawler instance.
     *
     * @param  Crawler  $crawler
     * @param  list<array{id: int, slug: string, url: string}>  &$urls
     */
    private function extractUrlsFromCrawler(Crawler $crawler, array &$urls): void
    {
        // Pattern 1: onclick="loadDetailsEvent(ID, 'slug', this)"
        $crawler->filter('[onclick*="loadDetailsEvent"]')->each(function (Crawler $node) use (&$urls) {
            $onclick = $node->attr('onclick') ?? '';
            if (preg_match('/loadDetailsEvent\(\s*(\d+)\s*,\s*[\'"]([^"\']+)[\'"]/i', $onclick, $m)) {
                $id = (int) $m[1];
                $slug = $m[2];
                $url = self::BASE_URL . '/' . $slug . '-' . $id;

                // Deduplicate by ID
                foreach ($urls as $existing) {
                    if ($existing['id'] === $id) {
                        return;
                    }
                }

                $urls[] = ['id' => $id, 'slug' => $slug, 'url' => $url];
            }
        });

        // Pattern 2: href="info-slug-id" links
        $crawler->filter('a[href*="info-"]')->each(function (Crawler $node) use (&$urls) {
            $href = $node->attr('href') ?? '';
            if (preg_match('/info-(.+?)-(\d+)$/', $href, $m)) {
                $slug = 'info-' . $m[1];
                $id = (int) $m[2];
                $url = self::BASE_URL . '/' . $slug . '-' . $id;

                foreach ($urls as $existing) {
                    if ($existing['id'] === $id) {
                        return;
                    }
                }

                $urls[] = ['id' => $id, 'slug' => $slug, 'url' => $url];
            }
        });
    }

    /**
     * Scrape a single competition detail from its listing card HTML.
     *
     * @param  Crawler  $card  A crawler positioned on a single .event-container element
     * @return array<string, mixed>
     */
    public function parseCardData(Crawler $card): array
    {
        $data = [
            'title' => '',
            'category' => 'Lainnya',
            'organizer' => '',
            'fee' => 0,
            'location' => '',
            'registration_deadline' => null,
            'event_start' => null,
            'event_end' => null,
            'poster_image' => null,
            'type' => null,
            'source_url' => null,
            'description' => null,
            'contact_person_phone' => null,
            'external_registration_url' => null,
            'official_website' => null,
            'status' => 'open',
        ];

        // Title
        try {
            $titleNode = $card->filter('.event-title a');
            if ($titleNode->count() > 0) {
                $data['title'] = trim($titleNode->text(''));
            }
        } catch (\Exception) {
        }

        // Poster image
        try {
            $imgNode = $card->filter('.img-container img');
            if ($imgNode->count() > 0) {
                $src = $imgNode->attr('src') ?? '';
                if ($src && !str_starts_with($src, 'http')) {
                    $src = self::BASE_URL . '/' . ltrim($src, '/');
                }
                $data['poster_image'] = $src;
            }
        } catch (\Exception) {
        }

        // Target audience (used for determining type)
        try {
            $targetNode = $card->filter('.target');
            if ($targetNode->count() > 0) {
                $targetText = trim($targetNode->text(''));
                $data['type'] = $this->mapTargetToType($targetText);
            }
        } catch (\Exception) {
        }

        // Fee
        try {
            $feeNode = $card->filter('.biaya');
            if ($feeNode->count() > 0) {
                $feeText = trim($feeNode->text(''));
                $data['fee'] = $this->parseFee($feeText);
            }
        } catch (\Exception) {
        }

        // Location
        try {
            $locNode = $card->filter('.lokasi');
            if ($locNode->count() > 0) {
                $data['location'] = trim($locNode->text(''));
            }
        } catch (\Exception) {
        }

        // Timeline / dates
        try {
            $dateNode = $card->filter('.tanggal');
            if ($dateNode->count() > 0) {
                $dateText = trim($dateNode->text(''));
                $dates = $this->parseDateRange($dateText);
                $data['event_start'] = $dates['start'];
                $data['event_end'] = $dates['end'];
                $data['registration_deadline'] = $dates['end']; // Use end date as deadline
            }
        } catch (\Exception) {
        }

        // Organizer
        try {
            $orgNode = $card->filter('.penyelenggara');
            if ($orgNode->count() > 0) {
                $spans = $orgNode->filter('span');
                if ($spans->count() >= 2) {
                    $data['organizer'] = trim($spans->eq(1)->text(''));
                } elseif ($spans->count() === 1) {
                    $data['organizer'] = trim($spans->eq(0)->text(''));
                }
            }
        } catch (\Exception) {
        }

        // Source URL from href (more reliable) or onclick
        try {
            $linkNode = $card->filter('.event-title a');
            if ($linkNode->count() > 0) {
                $href = $linkNode->first()->attr('href') ?? '';
                if ($href && $href !== '#') {
                    $data['source_url'] = $this->normalizeInfoLombaUrl($href);
                }
            }

            if (empty($data['source_url'])) {
                $onclickNode = $card->filter('[onclick*="loadDetailsEvent"]');
                if ($onclickNode->count() > 0) {
                    $onclick = $onclickNode->first()->attr('onclick') ?? '';
                    if (preg_match('/loadDetailsEvent\(\s*(\d+)\s*,\s*[\'"]([^"\']+)[\'"]/i', $onclick, $m)) {
                        $data['source_url'] = $this->normalizeInfoLombaUrl($m[2] . '-' . $m[1]);
                    }
                }
            }
        } catch (\Exception) {
        }

        return $data;
    }

    /**
     * Scrape full listing data from the homepage + AJAX batches.
     *
     * This method collects card data directly from the listing HTML,
     * avoiding the need to visit each detail page individually.
     *
     * @param  int  $totalNeeded
     * @param  callable|null  $onBatch  fn(int $collected, int $total)
     * @return list<array<string, mixed>>
     */
    public function scrapeListingCards(int $totalNeeded = 500, ?callable $onBatch = null): array
    {
        $results = [];

        // Phase 1: Scrape initial page
        $response = Http::timeout(30)->get(self::BASE_URL);
        if ($response->failed()) {
            throw new \RuntimeException('Failed to fetch infolomba.id homepage');
        }

        $crawler = new Crawler($response->body());
        $this->extractCardsFromCrawler($crawler, $results);

        if ($onBatch) {
            $onBatch(count($results), $totalNeeded);
        }

        // Phase 2: Load more batches
        $offset = self::BATCH_SIZE;

        while (count($results) < $totalNeeded) {
            try {
                $response = Http::timeout(30)
                    ->asForm()
                    ->post(self::LOAD_EVENT_URL, ['start' => $offset]);
            } catch (\Exception $e) {
                break;
            }

            if ($response->failed()) {
                break;
            }

            $json = $response->json();

            if (empty($json['html']) || ($json['event_count'] ?? 0) === 0) {
                break;
            }

            $batchCrawler = new Crawler('<div>' . $json['html'] . '</div>');
            $this->extractCardsFromCrawler($batchCrawler, $results);

            if ($onBatch) {
                $onBatch(count($results), $totalNeeded);
            }

            if (($json['event_count'] ?? 0) < self::BATCH_SIZE) {
                break;
            }

            $offset += self::BATCH_SIZE;

            // Polite delay
            usleep(500_000); // 500ms
        }

        return array_slice($results, 0, $totalNeeded);
    }

    /**
     * Extract card data from a crawler positioned on a page with .event-container elements.
     *
     * @param  Crawler  $crawler
     * @param  list<array<string, mixed>>  &$results
     */
    private function extractCardsFromCrawler(Crawler $crawler, array &$results): void
    {
        $crawler->filter('.event-container')->each(function (Crawler $card) use (&$results) {
            $data = $this->parseCardData($card);

            // Skip empty titles
            if (empty($data['title'])) {
                return;
            }

            // Deduplicate by source_url
            if ($data['source_url']) {
                foreach ($results as $existing) {
                    if ($existing['source_url'] === $data['source_url']) {
                        return;
                    }
                }
            }

            $results[] = $data;
        });
    }

    /**
     * Normalize and allowlist detail URLs that the scraper may fetch later.
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
     * Parse fee text like "Rp 65.000" or "Gratis" into a decimal value.
     */
    private function parseFee(string $text): float
    {
        $text = strtolower(trim($text));

        if (str_contains($text, 'gratis') || str_contains($text, 'free')) {
            return 0;
        }

        // Extract first number from patterns like "Rp 65.000" or "Rp 65.000 - Rp 90.000"
        if (preg_match('/rp\s*[.]?\s*([\d.]+)/', $text, $m)) {
            return (float) str_replace('.', '', $m[1]);
        }

        // Try plain number
        if (preg_match('/([\d.]+)/', $text, $m)) {
            return (float) str_replace('.', '', $m[1]);
        }

        return 0;
    }

    /**
     * Parse date range like "25 Mei - 8 Jun 2026" into start and end dates.
     *
     * @return array{start: string|null, end: string|null}
     */
    private function parseDateRange(string $text): array
    {
        $months = [
            'jan' => '01', 'feb' => '02', 'mar' => '03',
            'apr' => '04', 'mei' => '05', 'jun' => '06',
            'jul' => '07', 'agu' => '08', 'ags' => '08',
            'sep' => '09', 'okt' => '10', 'nov' => '11',
            'des' => '12',
        ];

        $text = trim($text);

        // Pattern: "25 Mei - 8 Jun 2026"
        if (preg_match('/(\d{1,2})\s+(\w{3})\s*-\s*(\d{1,2})\s+(\w{3})\s+(\d{4})/i', $text, $m)) {
            $startDay = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $startMonth = $months[strtolower(substr($m[2], 0, 3))] ?? '01';
            $endDay = str_pad($m[3], 2, '0', STR_PAD_LEFT);
            $endMonth = $months[strtolower(substr($m[4], 0, 3))] ?? '01';
            $year = $m[5];

            return [
                'start' => "{$year}-{$startMonth}-{$startDay} 00:00:00",
                'end'   => "{$year}-{$endMonth}-{$endDay} 23:59:59",
            ];
        }

        // Pattern: "25 Mei 2026"
        if (preg_match('/(\d{1,2})\s+(\w{3})\s+(\d{4})/i', $text, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = $months[strtolower(substr($m[2], 0, 3))] ?? '01';
            $year = $m[3];

            return [
                'start' => "{$year}-{$month}-{$day} 00:00:00",
                'end'   => "{$year}-{$month}-{$day} 23:59:59",
            ];
        }

        return ['start' => null, 'end' => null];
    }

    /**
     * Map target audience text to Competition::TYPES value.
     */
    private function mapTargetToType(string $targetText): string
    {
        $text = strtolower($targetText);

        if (str_contains($text, 'internasional') || str_contains($text, 'international')) {
            return 'Internasional';
        }

        if (str_contains($text, 'nasional')) {
            return 'Nasional';
        }

        if (str_contains($text, 'mahasiswa') || str_contains($text, 'universitas')) {
            return 'Nasional';
        }

        return 'Nasional'; // Default for infolomba.id events
    }
}
