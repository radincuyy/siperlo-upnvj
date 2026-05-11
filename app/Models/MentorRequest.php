<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $mentor_id
 * @property int $registration_id
 * @property string $reason
 * @property string $status
 * @property string|null $admin_notes
 */
class MentorRequest extends Model
{
    public const REVIEW_STATUSES = [
        'pending' => 'Menunggu Review',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];

    public const STATUSES = [
        'pending' => 'Menunggu Review',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'revision' => 'Perlu Revisi',
    ];
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'mentor_id',
        'registration_id',
        'reason',
        'status',
        'admin_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Mentor, $this>
     */
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Mentor::class);
    }

    /**
     * @return BelongsTo<Registration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status ?? '';
    }
}
