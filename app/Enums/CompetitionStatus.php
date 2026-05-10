<?php

declare(strict_types=1);

namespace App\Enums;

enum CompetitionStatus: string
{
    case Open = 'open';
    case Soon = 'soon';
    case Closed = 'closed';
    case Draft = 'draft';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Pendaftaran Buka',
            self::Soon => 'Akan Datang',
            self::Closed => 'Ditutup',
            self::Draft => 'Draft',
        };
    }

    public function isPubliclyVisible(): bool
    {
        return match ($this) {
            self::Open, self::Soon, self::Closed => true,
            self::Draft => false,
        };
    }
}
