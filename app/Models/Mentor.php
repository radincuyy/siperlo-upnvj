<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $expertise
 * @property string|null $bio
 * @property int $total_mentored
 * @property bool $is_active
 */
class Mentor extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'expertise',
        'bio',
        'total_mentored',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'total_mentored' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Registration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * @return HasMany<MentorRequest, $this>
     */
    public function requests(): HasMany
    {
        return $this->hasMany(MentorRequest::class);
    }

    /**
     * @return HasMany<MentorAchievement, $this>
     */
    public function achievements(): HasMany
    {
        return $this->hasMany(MentorAchievement::class);
    }
}
