<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $organizer
 * @property string $category
 * @property string|null $type
 * @property \Illuminate\Support\Carbon $registration_deadline
 * @property \Illuminate\Support\Carbon|null $event_start
 * @property \Illuminate\Support\Carbon|null $event_end
 * @property string|null $location
 * @property string $fee
 * @property string|null $poster_image
 * @property string|null $guidebook_file
 * @property string|null $contact_person_name
 * @property string|null $contact_person_phone
 * @property string|null $contact_person_email
 * @property string|null $official_website
 * @property string|null $social_media
 * @property string|null $external_registration_url
 * @property string|null $requirements
 * @property string|null $benefits
 * @property string|null $timeline
 * @property string $status
 */
class Competition extends Model
{
    public const CATEGORIES = [
        'Agama' => 'Agama',
        'Akuntansi' => 'Akuntansi',
        'Ambassador' => 'Ambassador',
        'Artikel' => 'Artikel',
        'Automotif' => 'Automotif',
        'Bahasa Asing' => 'Bahasa Asing',
        'Baris Berbaris' => 'Baris Berbaris',
        'Beasiswa' => 'Beasiswa',
        'Bisnis' => 'Bisnis',
        'Cerdas Cermat' => 'Cerdas Cermat',
        'Challenge' => 'Challenge',
        'Dance/Tari' => 'Dance/Tari',
        'Debat' => 'Debat',
        'Desain' => 'Desain',
        'E-sport' => 'E-sport',
        'English' => 'English',
        'Esai' => 'Esai',
        'Fashion Show' => 'Fashion Show',
        'Fotografi' => 'Fotografi',
        'Giveaway' => 'Giveaway',
        'Hukum' => 'Hukum',
        'Infografis' => 'Infografis',
        'IT' => 'IT',
        'Karya Tulis Ilmiah' => 'Karya Tulis Ilmiah',
        'Kesehatan' => 'Kesehatan',
        'Keuangan' => 'Keuangan',
        'MC/Protocol' => 'MC/Protocol',
        'Media Pembelajaran' => 'Media Pembelajaran',
        'Menggambar/Drawing/Ilustrasi' => 'Menggambar/Drawing/Ilustrasi',
        'Mewarnai' => 'Mewarnai',
        'Musik' => 'Musik',
        'News Anchor/Pembawa Berita' => 'News Anchor/Pembawa Berita',
        'Olahraga' => 'Olahraga',
        'Olimpiade' => 'Olimpiade',
        'Pajak' => 'Pajak',
        'Paper' => 'Paper',
        'Pelatihan' => 'Pelatihan',
        'Permainan' => 'Permainan',
        'Pidato' => 'Pidato',
        'PMR' => 'PMR',
        'Podcast' => 'Podcast',
        'Poster' => 'Poster',
        'Pramuka' => 'Pramuka',
        'Rally Games' => 'Rally Games',
        'Robot' => 'Robot',
        'Sastra' => 'Sastra',
        'Seminar' => 'Seminar',
        'Seni' => 'Seni',
        'Stand Up Comedy' => 'Stand Up Comedy',
        'Statistika/Data' => 'Statistika/Data',
        'Story Telling' => 'Story Telling',
        'Teknik' => 'Teknik',
        'Trading' => 'Trading',
        'Try Out' => 'Try Out',
        'UI/UX' => 'UI/UX',
        'Videografi/Film' => 'Videografi/Film',
        'Voice Over' => 'Voice Over',
        'Lainnya' => 'Lainnya',
    ];

    public const TYPES = [
        'Universitas' => 'Universitas',
        'Regional' => 'Regional',
        'Nasional' => 'Nasional',
        'Internasional' => 'Internasional',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'organizer',
        'category',
        'type',
        'registration_deadline',
        'event_start',
        'event_end',
        'location',
        'fee',
        'poster_image',
        'guidebook_file',
        'contact_person_name',
        'contact_person_phone',
        'contact_person_email',
        'official_website',
        'social_media',
        'external_registration_url',
        'requirements',
        'benefits',
        'timeline',
        'status',
        'source_url',
        'is_scraped',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'registration_deadline' => 'datetime',
            'event_start' => 'datetime',
            'event_end' => 'datetime',
            'fee' => 'decimal:2',
            'is_scraped' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Registration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * @param  Builder<Competition>  $query
     * @return Builder<Competition>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'soon', 'closed']);
    }

    public function isRegistrable(): bool
    {
        return $this->status === 'open'
            && $this->registration_deadline->isFuture();
    }

    public function displayStatus(): string
    {
        if ($this->status === 'open' && $this->registration_deadline->isPast()) {
            return 'closed';
        }

        return $this->status;
    }

    public function requirementsList(): \Illuminate\Support\Collection
    {
        return $this->splitLines($this->requirements);
    }

    public function benefitsList(): \Illuminate\Support\Collection
    {
        return $this->splitLines($this->benefits);
    }

    public function timelineList(): \Illuminate\Support\Collection
    {
        return $this->splitLines($this->timeline);
    }

    public function hasContactInfo(): bool
    {
        return filled($this->contact_person_name)
            || filled($this->contact_person_phone)
            || filled($this->contact_person_email);
    }

    private function splitLines(?string $value): \Illuminate\Support\Collection
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();
    }
}
