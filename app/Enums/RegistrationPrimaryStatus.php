<?php

declare(strict_types=1);

namespace App\Enums;

enum RegistrationPrimaryStatus: string
{
    case Registered = 'registered';
    case Ongoing = 'ongoing';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::Registered => 'Terdaftar',
            self::Ongoing => 'Berlangsung',
            self::Finished => 'Selesai',
        };
    }

    public function rank(): int
    {
        return match ($this) {
            self::Registered => 1,
            self::Ongoing => 2,
            self::Finished => 3,
        };
    }
}
