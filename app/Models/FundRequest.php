<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $registration_id
 * @property int $user_id
 * @property string $amount
 * @property string $purpose
 * @property string|null $description
 * @property string|null $proposal_file
 * @property string|null $supporting_docs
 * @property string $status
 * @property string|null $admin_notes
 */
class FundRequest extends Model
{
    /**
     * Backward-compat shim untuk view yang loop konstanta.
     * Sumber kebenaran: App\Enums\ReviewStatus.
     */
    public const REVIEW_STATUSES = [
        'pending' => 'Menunggu Review',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];
    /**
     * @var list<string>
     */
    protected $fillable = [
        'registration_id',
        'user_id',
        'amount',
        'purpose',
        'description',
        'proposal_file',
        'supporting_docs',
        'status',
        'admin_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Registration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        $status = ReviewStatus::tryFrom((string) $this->status);

        return $status?->label() ?? $this->status ?? '';
    }
}
