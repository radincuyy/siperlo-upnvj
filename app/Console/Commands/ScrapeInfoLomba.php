<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition;
use App\Services\InfoLombaScraperService;
use Illuminate\Console\Command;

class ScrapeInfoLomba extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'siperlo:scrape-infolomba
        {--count=500 : Number of competitions to scrape}
        {--dry-run : Preview data without saving to database}
        {--force : Overwrite existing competitions with the same source_url}';

    /**
     * The console command description.
     */
    protected $description = 'Scrape competition data from infolomba.id and insert into database';

    public function handle(InfoLombaScraperService $scraper): int
    {
        $count = (int) $this->option('count');
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $this->info("🔍 Starting infolomba.id scraper — target: {$count} competitions");

        if ($dryRun) {
            $this->warn('DRY RUN MODE — no data will be saved');
        }

        $this->newLine();

        // Phase 1: Scrape listing cards
        $this->info('📡 Fetching competition cards from infolomba.id...');

        $cards = $scraper->scrapeListingCards($count, function (int $collected, int $total) {
            $this->output->write("\r  Collected: {$collected} / {$total}");
        });

        $this->newLine();
        $cardCount = count($cards);
        $this->info("✅ Collected {$cardCount} competition cards");
        $this->newLine();

        if (empty($cards)) {
            $this->error('No competitions found. The site might be down or the structure changed.');
            return self::FAILURE;
        }

        // Phase 2: Insert into database
        $inserted = 0;
        $skipped = 0;
        $updated = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($cardCount);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $progressBar->setMessage('Processing...');
        $progressBar->start();

        foreach ($cards as $card) {
            try {
                // Validate essential fields
                if (empty($card['title']) || empty($card['registration_deadline'])) {
                    $skipped++;
                    $progressBar->setMessage("Skipped (missing data): " . ($card['title'] ?? 'unknown'));
                    $progressBar->advance();
                    continue;
                }

                // Check for duplicates by source_url
                if ($card['source_url']) {
                    $existing = Competition::where('source_url', $card['source_url'])->first();

                    if ($existing && !$force) {
                        $skipped++;
                        $progressBar->setMessage("Skipped (exists): {$card['title']}");
                        $progressBar->advance();
                        continue;
                    }

                    if ($existing && $force) {
                        if (!$dryRun) {
                            $existing->update($this->prepareData($card));
                        }
                        $updated++;
                        $progressBar->setMessage("Updated: {$card['title']}");
                        $progressBar->advance();
                        continue;
                    }
                }

                // Also check by title to avoid duplicates
                $existingByTitle = Competition::where('title', $card['title'])->first();
                if ($existingByTitle) {
                    if ($force) {
                        if (!$dryRun) {
                            $existingByTitle->update($this->prepareData($card));
                        }
                        $updated++;
                        $progressBar->setMessage("Updated (title match): {$card['title']}");
                        $progressBar->advance();
                        continue;
                    } else {
                        $skipped++;
                        $progressBar->setMessage("Skipped (title match): {$card['title']}");
                        $progressBar->advance();
                        continue;
                    }
                }

                if (!$dryRun) {
                    Competition::create($this->prepareData($card));
                }

                $inserted++;
                $progressBar->setMessage("Inserted: {$card['title']}");
            } catch (\Exception $e) {
                $errors++;
                $progressBar->setMessage("Error: " . substr($e->getMessage(), 0, 60));
            }

            $progressBar->advance();
        }

        $progressBar->setMessage('Done!');
        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total scraped', $cardCount],
                ['Inserted', $inserted],
                ['Updated', $updated],
                ['Skipped (duplicate/incomplete)', $skipped],
                ['Errors', $errors],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a dry run. Run without --dry-run to actually save data.');

            // Show sample data
            $this->newLine();
            $this->info('Sample data (first 5):');
            foreach (array_slice($cards, 0, 5) as $i => $card) {
                $this->line(sprintf(
                    '  %d. %s | %s | %s | Rp %s | %s',
                    $i + 1,
                    $card['title'],
                    $card['organizer'] ?: '-',
                    $card['location'] ?: '-',
                    number_format($card['fee'], 0, ',', '.'),
                    $card['registration_deadline'] ?? '-',
                ));
            }
        }

        $this->newLine();
        $this->info('🎉 Scraping complete!');

        return self::SUCCESS;
    }

    /**
     * Prepare card data for database insertion.
     *
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>
     */
    private function prepareData(array $card): array
    {
        $truncate = fn(?string $val) => $val !== null ? mb_substr($val, 0, 255) : null;

        return [
            'title' => $truncate($card['title'] ?? ''),
            'organizer' => $truncate($card['organizer'] ?: 'Tidak diketahui'),
            'category' => $truncate($card['category'] ?? 'Lainnya'),
            'type' => $truncate($card['type'] ?? 'Nasional'),
            'registration_deadline' => $card['registration_deadline'],
            'event_start' => $card['event_start'],
            'event_end' => $card['event_end'],
            'location' => $truncate($card['location'] ?? ''),
            'fee' => $card['fee'] ?? 0,
            'poster_image' => $truncate($card['poster_image'] ?? ''),
            'status' => $this->determineStatus($card),
            'source_url' => $truncate($card['source_url'] ?? ''),
            'description' => $card['description'] ?? null,
            'contact_person_phone' => $truncate($card['contact_person_phone'] ?? ''),
            'external_registration_url' => $truncate($card['external_registration_url'] ?? ''),
            'official_website' => $truncate($card['official_website'] ?? ''),
        ];
    }

    /**
     * Determine the status of a competition based on dates.
     */
    private function determineStatus(array $card): string
    {
        if (!empty($card['registration_deadline'])) {
            try {
                $deadline = new \DateTime($card['registration_deadline']);
                $now = new \DateTime();

                if ($deadline < $now) {
                    return 'closed';
                }

                return 'open';
            } catch (\Exception) {
                return 'open';
            }
        }

        return 'open';
    }
}
