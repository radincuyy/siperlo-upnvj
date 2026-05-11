<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
 * @property string|null $result_status
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

    public const PRIMARY_STATUSES = [
        'registered' => 'Terdaftar',
        'ongoing' => 'Berlangsung',
        'finished' => 'Selesai',
    ];

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

    /**
     * Lifecycle precedence:
     * - Laporan hasil final (approved/rejected) selalu menghasilkan "finished".
     * - Kalau belum, fallback ke kolom status mentah.
     */
    public function primaryStatus(): string
    {
        return match (true) {
            $this->hasFinalResult() => 'finished',
            $this->status === 'ongoing' => 'ongoing',
            $this->status === 'finished' => 'finished',
            default => 'registered',
        };
    }

    public function primaryStatusLabel(): string
    {
        return self::PRIMARY_STATUSES[$this->primaryStatus()] ?? $this->primaryStatus();
    }

    public function primaryStatusRank(): int
    {
        return match ($this->primaryStatus()) {
            'ongoing' => 2,
            'finished' => 3,
            default => 1,
        };
    }

    public function resultStatusLabel(): string
    {
        return self::RESULT_STATUSES[$this->result_status] ?? 'Belum Dilaporkan';
    }

    public function hasResultReport(): bool
    {
        return filled($this->result_status)
            || filled($this->result)
            || filled($this->result_description)
            || filled($this->result_proof_file)
            || $this->result_submitted_at !== null;
    }

    public function hasFinalResult(): bool
    {
        return in_array($this->result_status, self::FINAL_RESULT_STATUSES, true);
    }

    public function canReportResult(): bool
    {
        return $this->primaryStatus() === 'ongoing'
            && ($this->result_status === null || $this->result_status === 'revision');
    }

    public function canRequestOptionalSupport(): bool
    {
        return $this->primaryStatus() !== 'finished';
    }

    public function hasActiveMentorSupport(): bool
    {
        if (filled($this->mentor_id)) {
            return true;
        }

        $activeStatuses = ['pending', 'approved'];

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
        $activeStatuses = ['pending', 'approved'];

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

    /**
     * Tab "Terdaftar" di review admin & dashboard: belum ongoing/finished dan
     * laporan hasilnya belum final (approved/rejected).
     *
     * @param  Builder<Registration>  $query
     * @return Builder<Registration>
     */
    public function scopeRegisteredTab(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['ongoing', 'finished'])
            ->where(fn (Builder $q) => $q->whereNull('result_status')
                ->orWhereNotIn('result_status', self::FINAL_RESULT_STATUSES));
    }

    /**
     * Tab "Berlangsung": status ongoing dan hasil belum dilaporkan atau perlu revisi.
     *
     * @param  Builder<Registration>  $query
     * @return Builder<Registration>
     */
    public function scopeOngoingTab(Builder $query): Builder
    {
        return $query->where('status', 'ongoing')
            ->where(fn (Builder $q) => $q->whereNull('result_status')
                ->orWhere('result_status', 'revision'));
    }

    /**
     * Tab "Selesai": status finished atau hasil sudah final.
     *
     * @param  Builder<Registration>  $query
     * @return Builder<Registration>
     */
    public function scopeFinishedTab(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q->where('status', 'finished')
            ->orWhereIn('result_status', self::FINAL_RESULT_STATUSES));
    }

    /**
     * Tab "Validasi Hasil": laporan hasil menunggu validasi admin.
     *
     * @param  Builder<Registration>  $query
     * @return Builder<Registration>
     */
    public function scopeResultPendingTab(Builder $query): Builder
    {
        return $query->where('result_status', 'pending');
    }
}
