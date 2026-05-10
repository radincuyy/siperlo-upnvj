<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompetitionStatus;
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
        'Akademik' => 'Akademik',
        'Olahraga' => 'Olahraga',
        'Teknologi' => 'Teknologi',
        'Seni & Budaya' => 'Seni & Budaya',
        'Kewirausahaan' => 'Kewirausahaan',
        'Penelitian' => 'Penelitian',
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
        return $query->whereIn('status', [
            CompetitionStatus::Open->value,
            CompetitionStatus::Soon->value,
            CompetitionStatus::Closed->value,
        ]);
    }

    public function isRegistrable(): bool
    {
        return $this->status === CompetitionStatus::Open->value
            && $this->registration_deadline->isFuture();
    }
}
