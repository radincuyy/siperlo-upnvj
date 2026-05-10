<div align="center">

<img src="public/brand/siperlo-mark.png" alt="SIPERLO UPNVJ" width="96" />

# SIPERLO UPNVJ

Sistem Informasi Perlombaan Mahasiswa untuk Universitas Pembangunan Nasional Veteran Jakarta.

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Tailwind](https://img.shields.io/badge/Tailwind-3-38BDF8?logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3-8BC0D0?logo=alpine.js&logoColor=black)](https://alpinejs.dev/)

</div>

Platform operasional kampus yang memusatkan informasi lomba, mencatat partisipasi mahasiswa, menstandarkan SOP, dan memantau progres dari pendaftaran hingga laporan hasil. Dibangun untuk empat peran: mahasiswa, admin Kemahasiswaan, mentor, dan pimpinan.

SIPERLO bukan marketing page. Tampilan dirancang seperti operations desk: status eksplisit, aksi berikutnya jelas, dan tidak ada UI menyesatkan setelah proses selesai.

## Daftar Isi

- [Fitur utama](#fitur-utama)
- [Tech stack](#tech-stack)
- [Prasyarat](#prasyarat)
- [Getting started](#getting-started)
- [Akun seed](#akun-seed)
- [Struktur proyek](#struktur-proyek)
- [Prinsip desain](#prinsip-desain)
- [Pengujian](#pengujian)
- [Kredit](#kredit)

## Fitur utama

### Mahasiswa

- Mencari dan mendaftar lomba internal
- Memilih mentor pendamping (opsional)
- Mengajukan bantuan dana (opsional)
- Melaporkan hasil lomba setelah selesai

### Admin Kemahasiswaan

- Mengelola katalog lomba
- Review antrian pendaftaran, pengajuan mentor, dan pengajuan dana
- Validasi laporan hasil lomba dengan status lifecycle yang terkunci

### Mentor

- Dashboard pengajuan yang masuk dan daftar bimbingan aktif

### Pimpinan

- Dashboard monitoring read-only: partisipasi, prestasi, sebaran status

## Tech stack

| Area       | Tool                                                             |
| ---------- | ---------------------------------------------------------------- |
| Backend    | PHP 8.2+, Laravel 12, Laravel Socialite (Google OAuth)           |
| Frontend   | Blade, Tailwind CSS 3, Alpine.js                                 |
| Database   | MySQL 8 (utf8mb4), mudah diganti ke PostgreSQL atau SQLite       |
| Icon       | Lucide via `mallardduck/blade-lucide-icons`                      |
| Fonts      | Literata (display), Atkinson Hyperlegible (body), self-hosted    |
| Build      | Vite 6, PostCSS, Autoprefixer                                    |
| Test       | PHPUnit 11                                                       |

## Prasyarat

- PHP 8.2 atau lebih baru dengan ekstensi standar Laravel
- Composer 2
- Node.js 18 atau lebih baru + npm
- MySQL 8 (atau ganti `DB_CONNECTION` ke `sqlite` jika ingin tanpa server database)

## Getting started

```bash
git clone https://github.com/USERNAME/siperlo-upnvj.git
cd siperlo-upnvj

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Siapkan database kosong di MySQL bernama `siperlo_upnvj`, lalu jalankan migration dan seeder:

```bash
php artisan migrate:fresh --seed
```

Terakhir, jalankan asset build dan development server:

```bash
npm run build        # atau `npm run dev` untuk hot reload
php artisan serve
```

Buka `http://localhost:8000`. Aplikasi akan redirect ke halaman login.

> [!TIP]
> Untuk pengembangan penuh dengan queue listener, log viewer, dan Vite hot reload dalam satu perintah, jalankan `composer dev`.

> [!IMPORTANT]
> Login dengan Google memerlukan `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` di `.env`. Tanpa kedua value tersebut, tombol Google akan menampilkan pesan "belum dikonfigurasi" dan flow email/password tetap berfungsi.

## Akun seed

Database seeder menyiapkan enam akun demo untuk setiap peran.

| Role      | Email                    | Password   |
| --------- | ------------------------ | ---------- |
| Admin     | admin@siperlo.test       | `password` |
| Pimpinan  | pimpinan@siperlo.test    | `password` |
| Mentor    | rangga@siperlo.test      | `password` |
| Mentor    | ridwan@siperlo.test      | `password` |
| Mahasiswa | mahasiswa@siperlo.test   | `password` |
| Mahasiswa | nadia@siperlo.test       | `password` |

> [!WARNING]
> Seeder menggunakan password statis `password`. Jangan jalankan `db:seed` di environment production.

## Struktur proyek

```
app/
├── app/
│   ├── Enums/                 # Enum status lifecycle (ReviewStatus, ResultStatus, dsb.)
│   ├── Http/
│   │   ├── Controllers/       # Web + admin + auth controllers
│   │   ├── Middleware/        # RoleMiddleware (mahasiswa, admin, mentor, pimpinan)
│   │   └── Requests/          # Form request validation
│   └── Models/                # Eloquent models (Competition, Registration, Mentor, dst.)
├── database/
│   ├── migrations/            # Schema lomba, pendaftaran, mentor, dana, hasil
│   └── seeders/               # DatabaseSeeder dengan akun + lomba demo
├── resources/
│   ├── css/                   # Self-hosted font, design tokens, component classes
│   ├── js/                    # Alpine.js bootstrap
│   └── views/                 # Blade templates dengan design system siperlo
├── routes/
│   ├── web.php                # Routes mahasiswa, admin, mentor, pimpinan
│   └── auth.php               # Routes autentikasi (Breeze + Google)
└── tests/                     # Feature tests untuk flow autentikasi dan inti
```

## Prinsip desain

SIPERLO mengikuti lima prinsip yang dijaga di semua layar.

1. **Proses utama harus selalu terlihat.** Status lifecycle hanya tiga: Terdaftar, Berlangsung, Selesai.
2. **Opsional harus terasa opsional.** Mentor dan bantuan dana tidak menghalangi mahasiswa mengikuti lomba.
3. **Status final mengunci aksi.** Setelah disetujui atau ditolak final, halaman membaca sebagai arsip.
4. **Admin bekerja dari antrian, bukan tabel mentah.** Review dipisah berdasarkan status actionable.
5. **Satu layar menjawab pertanyaan berikutnya.** Mahasiswa tahu apa yang harus dilakukan, admin tahu apa yang menunggu.

Detail design system (warna, tipografi, komponen) ada di `../DESIGN.md`.

## Pengujian

```bash
php artisan test
```

## Kredit

- Fonts: [Literata](https://fonts.google.com/specimen/Literata) dan [Atkinson Hyperlegible](https://brailleinstitute.org/freefont) (self-hosted).
- Icons: [Lucide](https://lucide.dev/) via [`mallardduck/blade-lucide-icons`](https://github.com/mallardduck/blade-lucide-icons).
- Framework: [Laravel](https://laravel.com/) + [Tailwind CSS](https://tailwindcss.com/) + [Alpine.js](https://alpinejs.dev/).

Dibangun untuk kebutuhan layanan kemahasiswaan di UPN Veteran Jakarta.
