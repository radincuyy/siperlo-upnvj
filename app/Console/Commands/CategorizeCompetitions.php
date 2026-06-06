<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition;
use Illuminate\Console\Command;

class CategorizeCompetitions extends Command
{
    protected $signature = 'siperlo:categorize
        {--dry-run : Preview without saving}';

    protected $description = 'Auto-categorize competitions based on title keywords';

    /**
     * Keyword → category mapping.
     * Order matters: more specific patterns should come first.
     *
     * @var array<string, list<string>>
     */
    private const KEYWORD_MAP = [
        'Olimpiade' => ['olimpiade', 'olympiad', 'osn', 'ksn', 'osk', 'ksp'],
        'Debat' => ['debat', 'debate', 'moot court', 'mootcourt'],
        'Esai' => ['esai', 'essay', 'essai'],
        'Karya Tulis Ilmiah' => ['karya tulis', 'kti', 'lkti', 'paper competition', 'scientific paper', 'penelitian', 'riset'],
        'Paper' => ['paper', 'call for paper', 'jurnal'],
        'Artikel' => ['artikel', 'article', 'opini', 'menulis', 'writing competition', 'blog', 'story writing', 'cerpen', 'puisi'],
        'Desain' => ['desain', 'design', 'logo', 'branding', 'canva'],
        'UI/UX' => ['ui/ux', 'ui ux', 'uiux', 'user interface', 'user experience'],
        'IT' => ['hackathon', 'hack', 'coding', 'programming', 'software', 'web dev', 'app dev', 'data science', 'data analyst', 'machine learning', 'artificial intelligence', 'ai ', 'gemastik', 'it ', 'cyber', 'ctf', 'iot', 'blockchain', 'robotic'],
        'Robot' => ['robot', 'robotik', 'robotics', 'drone'],
        'Fotografi' => ['fotografi', 'foto', 'photo', 'photography'],
        'Videografi/Film' => ['video', 'film', 'short movie', 'movie', 'sinematografi', 'cinemat', 'reels', 'tiktok', 'vlog', 'microfilm', 'dokumenter'],
        'Poster' => ['poster'],
        'Infografis' => ['infografis', 'infographic'],
        'Olahraga' => ['basket', 'futsal', 'badminton', 'voli', 'volley', 'renang', 'atletik', 'bulu tangkis', 'sepak bola', 'football', 'tennis', 'tenis', 'marathon', 'lari', 'lima basketball', 'catur', 'chess', 'pencak silat', 'karate', 'taekwondo', 'swimming', 'sport', 'porkab', 'porprov', 'archery', 'panahan'],
        'E-sport' => ['e-sport', 'esport', 'mobile legend', 'valorant', 'pubg', 'free fire', 'mlbb', 'dota', 'gaming'],
        'Musik' => ['musik', 'music', 'band', 'choir', 'paduan suara', 'nasyid', 'vokal', 'vocal', 'sing', 'festival band', 'solo vocal', 'solo vokal', 'piano', 'gitar'],
        'Dance/Tari' => ['tari', 'dance', 'dancing', 'k-pop', 'kpop', 'cover dance'],
        'Seni' => ['seni', 'art ', 'mural', 'lukis', 'paint', 'kaligrafi', 'batik', 'kerajinan', 'craft'],
        'Menggambar/Drawing/Ilustrasi' => ['gambar', 'drawing', 'ilustrasi', 'illustration', 'sketch', 'manga', 'komik', 'comic', 'mewarnai', 'coloring'],
        'Bisnis' => ['bisnis', 'business', 'entrepreneur', 'wirausaha', 'startup', 'marketing', 'business plan', 'bmc', 'e-commerce'],
        'Keuangan' => ['keuangan', 'finance', 'akuntansi', 'accounting', 'investasi', 'stock', 'saham'],
        'Pidato' => ['pidato', 'speech', 'orasi', 'public speaking'],
        'English' => ['english', 'spelling bee', 'storytelling english', 'speech english', 'english speech', 'english debate'],
        'Bahasa Asing' => ['bahasa jepang', 'bahasa arab', 'bahasa mandarin', 'bahasa korea', 'japanese', 'arabic', 'mandarin', 'korean'],
        'Cerdas Cermat' => ['cerdas cermat', 'quiz', 'kuis', 'trivia'],
        'Hukum' => ['hukum', 'law', 'legal', 'peradilan', 'constitutional'],
        'Kesehatan' => ['kesehatan', 'health', 'medis', 'medical', 'farmasi', 'pharmacy', 'gizi', 'nutrition', 'biomedical', 'kedokteran'],
        'Agama' => ['agama', 'tahfidz', 'hafiz', 'quran', 'qur\'an', 'tilawah', 'dai', 'dakwah', 'islamic', 'musabaqah', 'mhq', 'mtq', 'adzan', 'sholawat'],
        'Teknik' => ['teknik', 'engineering', 'mesin', 'sipil', 'elektro', 'civil', 'mechanical', 'bridge'],
        'Statistika/Data' => ['statistik', 'statistic', 'data', 'big data'],
        'PMR' => ['pmr', 'palang merah', 'pertolongan pertama', 'p3k'],
        'Pramuka' => ['pramuka', 'scout', 'jambore'],
        'Ambassador' => ['ambassador', 'duta', 'pageant', 'putera puteri', 'putra putri'],
        'Podcast' => ['podcast'],
        'Voice Over' => ['voice over', 'dubbing'],
        'News Anchor/Pembawa Berita' => ['news anchor', 'pembawa berita', 'news reading', 'newsreading', 'news reader'],
        'MC/Protocol' => ['mc ', 'master of ceremony', 'protokol', 'protocol'],
        'Stand Up Comedy' => ['stand up', 'standup', 'comedy', 'komedi'],
        'Story Telling' => ['storytelling', 'story telling', 'mendongeng', 'dongeng'],
        'Fashion Show' => ['fashion', 'busana', 'modeling'],
        'Baris Berbaris' => ['baris berbaris', 'pbb', 'parade', 'marching'],
        'Trading' => ['trading', 'forex'],
        'Rally Games' => ['rally', 'outbound'],
        'Try Out' => ['try out', 'tryout'],
        'Seminar' => ['seminar', 'webinar', 'workshop', 'bootcamp', 'pelatihan', 'training', 'conference', 'konferensi'],
        'Beasiswa' => ['beasiswa', 'scholarship'],
        'Pajak' => ['pajak', 'tax'],
        'Media Pembelajaran' => ['media pembelajaran', 'alat peraga'],
        'Challenge' => ['challenge', 'tantangan'],
        'Permainan' => ['permainan', 'game', 'board game'],
        'Giveaway' => ['giveaway', 'give away'],
        'Mewarnai' => ['mewarnai', 'coloring'],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $competitions = Competition::where('category', 'Lainnya')->get();

        $this->info("Found {$competitions->count()} competitions with category 'Lainnya'");

        if ($competitions->isEmpty()) {
            $this->info('Nothing to categorize.');
            return self::SUCCESS;
        }

        $categorized = 0;
        $unchanged = 0;

        foreach ($competitions as $competition) {
            $newCategory = $this->detectCategory($competition->title);

            if ($newCategory !== 'Lainnya') {
                if (!$dryRun) {
                    $competition->update(['category' => $newCategory]);
                }
                $categorized++;
                $this->line("  ✅ <info>{$newCategory}</info> ← {$competition->title}");
            } else {
                $unchanged++;
            }
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Categorized', $categorized],
                ['Still Lainnya', $unchanged],
                ['Total', $competitions->count()],
            ]
        );

        if ($dryRun) {
            $this->warn('Dry run — no changes saved. Run without --dry-run to apply.');
        }

        return self::SUCCESS;
    }

    private function detectCategory(string $title): string
    {
        $titleLower = mb_strtolower($title);

        foreach (self::KEYWORD_MAP as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($titleLower, strtolower($keyword))) {
                    return $category;
                }
            }
        }

        return 'Lainnya';
    }
}
