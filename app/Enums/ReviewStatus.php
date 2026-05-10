<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Shared review lifecycle untuk MentorRequest dan FundRequest.
 */
enum ReviewStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Revision = 'revision';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Review',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::Revision => 'Perlu Revisi',
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::Approved, self::Rejected => true,
            self::Pending, self::Revision => false,
        };
    }
}
