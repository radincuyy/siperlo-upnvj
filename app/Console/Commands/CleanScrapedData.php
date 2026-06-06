<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition;
use Illuminate\Console\Command;

class CleanScrapedData extends Command
{
    protected $signature = 'siperlo:clean-scraped
        {--dry-run : Preview without saving}';

    protected $description = 'Clean scraped competition data: set is_scraped flag, parse locations from descriptions';

    /**
     * Patterns to extract location from description text.
     * Each pattern tries to capture a meaningful location string.
     */
    private const LOCATION_PATTERNS = [
        // 📍 Location text or 📍 text
        '/📍\s*([^\n🔥🎉✨🎯💡🔺📌🎁🏆💰📞\r]{3,60})/u',
        // Lokasi: XYZ or Location: XYZ
        '/(?:lokasi|location|tempat)\s*[:]\s*([^\n\r🔥✨]{3,80})/iu',
        // Venue patterns like "di Aula/Gedung/Kampus..."
        '/(?:di|at)\s+((?:Aula|Gedung|Kampus|Universitas|Hotel|Ruang|Hall|Auditorium|Convention|Ballroom)[^\n\r,]{3,60})/iu',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Step 1: Mark all competitions with source_url as is_scraped
        $this->info('🏷️  Marking scraped competitions...');
        $scrapedCount = Competition::whereNotNull('source_url')
            ->where('source_url', '!=', '')
            ->where('is_scraped', false)
            ->count();

        if (!$dryRun) {
            Competition::whereNotNull('source_url')
                ->where('source_url', '!=', '')
                ->update(['is_scraped' => true]);
        }
        $this->info("  → {$scrapedCount} competitions marked as is_scraped");

        // Step 2: Parse locations from descriptions for entries with empty locations
        $this->info('');
        $this->info('📍 Parsing locations from descriptions...');

        $needLocation = Competition::where('is_scraped', true)
            ->where(function ($q) {
                $q->whereNull('location')
                  ->orWhere('location', '')
                  ->orWhere('location', 'Online')
                  ->orWhere('location', 'Nasional');
            })
            ->whereNotNull('description')
            ->get();

        $this->info("  Found {$needLocation->count()} competitions with generic/empty locations");

        $fixed = 0;
        foreach ($needLocation as $comp) {
            $location = $this->extractLocationFromDescription($comp->description);

            if ($location) {
                if (!$dryRun) {
                    $comp->update(['location' => $location]);
                }
                $this->line("  ✅ [{$comp->id}] {$location} ← " . substr($comp->title, 0, 40));
                $fixed++;
            }
        }

        $this->info("  → {$fixed} locations extracted");

        // Step 3: Summary
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Marked is_scraped', $scrapedCount],
                ['Locations fixed', $fixed],
                ['Total scraped', Competition::where('is_scraped', true)->count()],
                ['Still empty location', Competition::where('is_scraped', true)->where(function ($q) {
                    $q->whereNull('location')->orWhere('location', '')->orWhere('location', 'Online')->orWhere('location', 'Nasional');
                })->count()],
            ]
        );

        if ($dryRun) {
            $this->warn('Dry run — no changes saved.');
        }

        $this->info('🎉 Cleanup complete!');
        return self::SUCCESS;
    }

    /**
     * Try to extract a meaningful location from description text.
     */
    private function extractLocationFromDescription(string $description): ?string
    {
        foreach (self::LOCATION_PATTERNS as $pattern) {
            if (preg_match($pattern, $description, $m)) {
                $location = trim($m[1]);
                // Clean up common trailing chars
                $location = rtrim($location, " \t\n\r.,:;!?🔥✨🎉");
                $location = preg_replace('/\s+/', ' ', $location);

                // Skip if it's too generic
                if (in_array(mb_strtolower($location), ['online', 'nasional', 'indonesia', '-', ''])) {
                    continue;
                }

                // Skip if it's too short
                if (mb_strlen($location) < 3) {
                    continue;
                }

                return $location;
            }
        }

        return null;
    }
}
