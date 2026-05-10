<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Pimpinan = 'pimpinan';
    case Mentor = 'mentor';
    case Mahasiswa = 'mahasiswa';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin Kemahasiswaan',
            self::Pimpinan => 'Pimpinan',
            self::Mentor => 'Mentor',
            self::Mahasiswa => 'Mahasiswa',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Pimpinan => 'pimpinan.dashboard',
            self::Mentor => 'mentor.dashboard',
            self::Mahasiswa => 'competitions.index',
        };
    }
}
