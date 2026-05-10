<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string|null $nim_nip
 * @property string|null $faculty
 * @property string|null $major
 * @property string|null $phone
 * @property string|null $google_id
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nim_nip',
        'faculty',
        'major',
        'phone',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
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
     * @return HasOne<Mentor, $this>
     */
    public function mentor(): HasOne
    {
        return $this->hasOne(Mentor::class);
    }

    /**
     * @return HasMany<MentorRequest, $this>
     */
    public function mentorRequests(): HasMany
    {
        return $this->hasMany(MentorRequest::class);
    }

    /**
     * @return HasMany<FundRequest, $this>
     */
    public function fundRequests(): HasMany
    {
        return $this->hasMany(FundRequest::class);
    }

    public function isRole(UserRole|string ...$roles): bool
    {
        foreach ($roles as $role) {
            $value = $role instanceof UserRole ? $role->value : $role;
            if ($this->role === $value) {
                return true;
            }
        }

        return false;
    }

    public function dashboardRoute(): string
    {
        $role = UserRole::tryFrom((string) $this->role) ?? UserRole::Mahasiswa;

        return $role->dashboardRoute();
    }
}
