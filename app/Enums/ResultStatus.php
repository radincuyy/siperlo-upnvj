<?php

declare(strict_types=1);

namespace App\Enums;

enum ResultStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Revision = 'revision';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Validasi',
            self::Approved => 'Disetujui',
            self::Revision => 'Perlu Revisi',
            self::Rejected => 'Ditolak',
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
