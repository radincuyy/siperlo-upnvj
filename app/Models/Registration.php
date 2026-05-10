<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RegistrationPrimaryStatus;
use App\Enums\ResultStatus;
use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $user_id
 * @property int $competition_id
 * @property int|null $mentor_id
 * @property string $status Raw DB status (registered|ongoing|finished|mentor_pending|...).
 * @property string|null $result
 * @property ResultStatus|null $result_status
 * @property string|null $result_description
 * @property string|null $result_proof_file
 * @property \Illuminate\Support\Carbon|null $result_submitted_at
 * @property \Illuminate\Support\Carbon|null $result_reviewed_at
 * @property string|null $result_admin_notes
 * @property string|null $notes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MentorRequest> $mentorRequests
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FundRequest> $fundRequests
 */
class Registration extends Model
{
    public const PROGRESS_STEPS = [
        'registered' => 'Terdaftar',
        'ongoing' => 'Berlangsung',
        'finished' => 'Hasil',
    ];

    /**
     * Backward-compat shim untuk view yang masih render select/loop dari konstanta.
     * Sumber kebenaran: App\Enums\RegistrationPrimaryStatus.
     */
    public const PRIMARY_STATUSES = [
        'registered' => 'Terdaftar',
        'ongoing' => 'Berlangsung',
        'finished' => 'Selesai',
    ];

    /**
     * Backward-compat shim untuk view.
     * Sumber kebenaran: App\Enums\ResultStatus.
     */
    public const RESULT_STATUSES = [
        'pending' => 'Menunggu Validasi',
        'approved' => 'Disetujui',
        'revision' => 'Perlu Revisi',
        'rejected' => 'Ditolak',
    ];

    public const FINAL_RESULT_STATUSES = ['approved', 'rejected'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'competition_id',
        'mentor_id',
        'status',
        'result',
        'result_status',
        'result_description',
        'result_proof_file',
        'result_submitted_at',
        'result_reviewed_at',
        'result_admin_notes',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'result_submitted_at' => 'datetime',
            'result_reviewed_at' => 'datetime',
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
     * @return BelongsTo<Competition, $this>
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * @return BelongsTo<Mentor, $this>
     */
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Mentor::class);
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

    /**
     * @return HasOne<FundRequest, $this>
     */
    public function latestFundRequest(): HasOne
    {
        return $this->hasOne(FundRequest::class)->latestOfMany();
    }

    public function primaryStatus(): RegistrationPrimaryStatus
    {
        return match (true) {
            $this->hasFinalResult() => RegistrationPrimaryStatus::Finished,
            $this->status === 'ongoing' => RegistrationPrimaryStatus::Ongoing,
            $this->status === 'finished' => RegistrationPrimaryStatus::Finished,
            default => RegistrationPrimaryStatus::Registered,
        };
    }

    public function primaryStatusLabel(): string
    {
        return $this->primaryStatus()->label();
    }

    public function primaryStatusRank(): int
    {
        return $this->primaryStatus()->rank();
    }

    public function resultStatusLabel(): string
    {
        $status = ResultStatus::tryFrom((string) $this->result_status);

        return $status?->label() ?? 'Belum Dilaporkan';
    }

    public function hasResultReport(): bool
    {
        return $this->result_status !== null
            || filled($this->result)
            || filled($this->result_description)
            || filled($this->result_proof_file)
            || $this->result_submitted_at !== null;
    }

    public function hasFinalResult(): bool
    {
        $status = ResultStatus::tryFrom((string) $this->result_status);

        return $status?->isFinal() ?? false;
    }

    public function canReportResult(): bool
    {
        return $this->primaryStatus() === RegistrationPrimaryStatus::Ongoing
            && ($this->result_status === null || $this->result_status === ResultStatus::Revision->value);
    }

    public function canRequestOptionalSupport(): bool
    {
        return $this->primaryStatus() !== RegistrationPrimaryStatus::Finished;
    }

    public function hasActiveMentorSupport(): bool
    {
        if (filled($this->mentor_id)) {
            return true;
        }

        $activeStatuses = [ReviewStatus::Pending->value, ReviewStatus::Approved->value];

        if ($this->relationLoaded('mentorRequests')) {
            return $this->mentorRequests
                ->contains(fn (MentorRequest $r) => in_array($r->status, $activeStatuses, true));
        }

        return $this->mentorRequests()
            ->whereIn('status', $activeStatuses)
            ->exists();
    }

    public function canRequestMentor(): bool
    {
        return $this->canRequestOptionalSupport()
            && ! $this->hasActiveMentorSupport();
    }

    public function hasActiveFundSupport(): bool
    {
        $activeStatuses = [ReviewStatus::Pending->value, ReviewStatus::Approved->value];

        if ($this->relationLoaded('fundRequests')) {
            return $this->fundRequests
                ->contains(fn (FundRequest $r) => in_array($r->status, $activeStatuses, true));
        }

        return $this->fundRequests()
            ->whereIn('status', $activeStatuses)
            ->exists();
    }

    public function canRequestFund(): bool
    {
        return $this->canRequestOptionalSupport()
            && ! $this->hasActiveFundSupport();
    }
}
