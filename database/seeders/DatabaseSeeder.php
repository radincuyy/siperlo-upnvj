<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\FundRequest;
use App\Models\Mentor;
use App\Models\MentorAchievement;
use App\Models\MentorRequest;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->createUser([
            'name' => 'Admin Kemahasiswaan',
            'email' => 'admin@siperlo.test',
            'password' => Hash::make('password'),
            'nim_nip' => 'ADM001',
            'faculty' => 'FIK',
            'phone' => '081200000001',
        ], role: 'admin');

        $pimpinan = $this->createUser([
            'name' => 'Pimpinan Fakultas',
            'email' => 'pimpinan@siperlo.test',
            'password' => Hash::make('password'),
            'nim_nip' => 'PIM001',
            'faculty' => 'FIK',
            'phone' => '081200000002',
        ], role: 'pimpinan');

        $mentorUsers = collect([
            [
                'name' => 'Dr. Rangga Prasetya',
                'email' => 'rangga@siperlo.test',
                'nim_nip' => '198801012020121001',
                'expertise' => 'Debat Bahasa Inggris, Public Speaking, Argumentasi',
                'bio' => 'Berpengalaman membimbing tim debat tingkat nasional dan seleksi mahasiswa berprestasi.',
            ],
            [
                'name' => 'Dr. Ridwan Arif',
                'email' => 'ridwan@siperlo.test',
                'nim_nip' => '198706152019031002',
                'expertise' => 'Teknologi Informasi, UI/UX, Data Science',
                'bio' => 'Fokus membimbing kompetisi inovasi digital, pengembangan aplikasi, dan karya tulis teknologi.',
            ],
        ])->map(function (array $data) {
            $user = $this->createUser([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'nim_nip' => $data['nim_nip'],
                'faculty' => 'FIK',
            ], role: 'mentor');

            return Mentor::create([
                'user_id' => $user->id,
                'expertise' => $data['expertise'],
                'bio' => $data['bio'],
                'total_mentored' => 4,
                'is_active' => true,
            ]);
        });

        $studentA = $this->createUser([
            'name' => 'Radin Mahasiswa',
            'email' => 'mahasiswa@siperlo.test',
            'password' => Hash::make('password'),
            'nim_nip' => '2210511001',
            'faculty' => 'FIK',
            'major' => 'Sistem Informasi',
            'phone' => '081200000003',
        ], role: 'mahasiswa');

        $studentB = $this->createUser([
            'name' => 'Nadia Putri',
            'email' => 'nadia@siperlo.test',
            'password' => Hash::make('password'),
            'nim_nip' => '2210511002',
            'faculty' => 'FIK',
            'major' => 'Informatika',
            'phone' => '081200000004',
        ], role: 'mahasiswa');

        $competitions = collect([
            [
                'title' => 'National University Debating Championship (NUDC) 2024',
                'description' => 'Kompetisi debat bahasa Inggris tingkat nasional untuk mengasah argumentasi, riset, dan komunikasi mahasiswa.',
                'organizer' => 'Puspresnas',
                'category' => 'Akademik',
                'type' => 'Nasional',
                'registration_deadline' => now()->addDays(14),
                'event_start' => now()->addMonth(),
                'event_end' => now()->addMonth()->addDays(3),
                'location' => 'Jakarta',
                'fee' => 0,
                'status' => 'open',
                'contact_person_name' => 'Sekretariat NUDC',
                'contact_person_phone' => '081234567801',
                'contact_person_email' => 'nudc@puspresnas.id',
                'official_website' => 'https://pusatprestasinasional.kemdikbud.go.id',
                'social_media' => 'https://www.instagram.com/puspresnas',
                'external_registration_url' => 'https://pusatprestasinasional.kemdikbud.go.id',
                'requirements' => "Mahasiswa aktif program sarjana atau diploma.\nMenguasai debat bahasa Inggris.\nMengikuti ketentuan internal delegasi kampus.\nBersedia mengikuti pembinaan mentor.",
                'benefits' => "Sertifikat peserta atau pemenang.\nPeluang delegasi nasional.\nPenguatan portofolio prestasi dan SKPI.\nPembinaan public speaking dan argumentasi.",
                'timeline' => "13 Mei 2026 - Batas pendaftaran internal.\n15 Mei 2026 - Seleksi dan validasi delegasi.\n20 Mei 2026 - Pembinaan intensif bersama mentor.\n29 Mei 2026 - Pelaksanaan kompetisi.",
            ],
            [
                'title' => 'Liga Mahasiswa (LIMA) Basketball Jabodetabek',
                'description' => 'Turnamen olahraga basket mahasiswa tingkat regional Jabodetabek.',
                'organizer' => 'LIMA',
                'category' => 'Olahraga',
                'type' => 'Regional',
                'registration_deadline' => now()->addDays(3),
                'event_start' => now()->addWeeks(3),
                'event_end' => now()->addWeeks(4),
                'location' => 'Gelanggang Mahasiswa Jakarta',
                'fee' => 500000,
                'status' => 'open',
                'contact_person_name' => 'Panitia LIMA Basketball',
                'contact_person_phone' => '081234567802',
                'contact_person_email' => 'basketball@liga-mahasiswa.com',
                'official_website' => 'https://www.liga-mahasiswa.com',
                'social_media' => 'https://www.instagram.com/ligamahasiswaofficial',
                'external_registration_url' => 'https://www.liga-mahasiswa.com',
                'requirements' => "Mahasiswa aktif dan terdaftar pada tim kampus.\nSatu tim mengikuti jumlah pemain sesuai regulasi.\nMelampirkan identitas mahasiswa dan surat delegasi.\nMengikuti technical meeting sebelum pertandingan.",
                'benefits' => "Pengalaman kompetisi regional.\nSertifikat dan publikasi tim.\nPeluang pembinaan prestasi olahraga kampus.\nPeningkatan rekam prestasi kemahasiswaan.",
                'timeline' => "3 hari lagi - Batas pendaftaran.\nMinggu berikutnya - Verifikasi berkas tim.\nH-3 pertandingan - Technical meeting.\nPekan pelaksanaan - Pertandingan regional.",
            ],
            [
                'title' => 'Gemastik XVI',
                'description' => 'Pagelaran mahasiswa nasional bidang TIK dengan kategori pengembangan perangkat lunak, UI/UX, data mining, dan keamanan siber.',
                'organizer' => 'Kemdikbudristek',
                'category' => 'Teknologi',
                'type' => 'Nasional',
                'registration_deadline' => now()->addDays(45),
                'event_start' => now()->addMonths(2),
                'event_end' => now()->addMonths(2)->addDays(5),
                'location' => 'Hybrid',
                'fee' => 0,
                'status' => 'open',
                'contact_person_name' => 'Helpdesk Gemastik',
                'contact_person_phone' => '081234567803',
                'contact_person_email' => 'info@gemastik.id',
                'official_website' => 'https://gemastik.kemdikbud.go.id',
                'social_media' => 'https://www.instagram.com/gemastik',
                'external_registration_url' => 'https://gemastik.kemdikbud.go.id',
                'requirements' => "Mahasiswa aktif perguruan tinggi.\nMembentuk tim sesuai kategori lomba.\nMenyiapkan proposal atau karya sesuai bidang.\nMengikuti seleksi internal sebelum submit eksternal.",
                'benefits' => "Sertifikat nasional.\nPeluang finalis dan juara nasional.\nPortofolio produk digital.\nPembinaan mentor sesuai kategori teknologi.",
                'timeline' => "13 Juni 2026 - Batas pendaftaran internal.\nMinggu kedua Juni - Review konsep dan tim.\nMinggu ketiga Juni - Submit karya.\nAkhir Juni - Pengumuman tahap berikutnya.",
            ],
            [
                'title' => 'Pekan Seni Mahasiswa Tingkat Daerah (Peksimida)',
                'description' => 'Ajang seni budaya mahasiswa tingkat daerah untuk bidang vokal, tari, fotografi, dan desain poster.',
                'organizer' => 'BPSMI DKI Jakarta',
                'category' => 'Seni & Budaya',
                'type' => 'Universitas',
                'registration_deadline' => now()->addDays(60),
                'event_start' => now()->addMonths(3),
                'event_end' => now()->addMonths(3)->addDays(2),
                'location' => 'DKI Jakarta',
                'fee' => 150000,
                'status' => 'soon',
                'contact_person_name' => 'BPSMI DKI Jakarta',
                'contact_person_phone' => '081234567804',
                'contact_person_email' => 'info@bpsmi-dki.or.id',
                'official_website' => 'https://bpsmi.or.id',
                'social_media' => 'https://www.instagram.com/bpsmi_official',
                'external_registration_url' => 'https://bpsmi.or.id',
                'requirements' => "Mahasiswa aktif.\nMemilih satu cabang seni sesuai minat.\nMenyiapkan karya atau portofolio.\nMengikuti seleksi dan arahan pembina kampus.",
                'benefits' => "Sertifikat kegiatan seni mahasiswa.\nPublikasi karya.\nPeluang mewakili kampus di tingkat daerah.\nPenguatan portofolio non-akademik.",
                'timeline' => "28 Juni 2026 - Batas pendaftaran internal.\nAwal Juli - Seleksi karya internal.\nPertengahan Juli - Validasi peserta.\nAkhir Juli - Pelaksanaan Peksimida.",
            ],
        ])->map(fn (array $data) => Competition::create($data));

        $registrationA = Registration::create([
            'user_id' => $studentA->id,
            'competition_id' => $competitions[0]->id,
            'mentor_id' => $mentorUsers[0]->id,
            'status' => 'registered',
            'notes' => 'Mahasiswa sudah terpantau sebagai peserta NUDC. Mentor bersifat pendamping opsional.',
        ]);

        $registrationB = Registration::create([
            'user_id' => $studentB->id,
            'competition_id' => $competitions[2]->id,
            'status' => 'registered',
            'notes' => 'Pengajuan dana delegasi sedang direview tanpa mengubah status utama lomba.',
        ]);

        MentorRequest::create([
            'user_id' => $studentA->id,
            'mentor_id' => $mentorUsers[0]->id,
            'registration_id' => $registrationA->id,
            'reason' => 'Membutuhkan pembimbing debat yang berpengalaman untuk penyusunan case dan latihan speaking.',
            'status' => 'approved',
            'admin_notes' => 'Disetujui karena bidang mentor sesuai dengan lomba.',
        ]);

        MentorRequest::create([
            'user_id' => $studentB->id,
            'mentor_id' => $mentorUsers[1]->id,
            'registration_id' => $registrationB->id,
            'reason' => 'Tim membutuhkan arahan UI/UX dan validasi konsep produk digital.',
            'status' => 'pending',
        ]);

        FundRequest::create([
            'user_id' => $studentB->id,
            'registration_id' => $registrationB->id,
            'amount' => 1250000,
            'purpose' => 'Biaya registrasi dan persiapan delegasi Gemastik',
            'description' => 'Dana digunakan untuk pendaftaran, transport lokal, dan kebutuhan presentasi final.',
            'status' => 'pending',
        ]);

        MentorAchievement::create([
            'mentor_id' => $mentorUsers[0]->id,
            'competition_name' => 'NUDC Regional 2023',
            'student_name' => 'Tim Debat FIK UPNVJ',
            'result' => 'Juara 2 Regional',
            'year' => 2023,
        ]);

        MentorAchievement::create([
            'mentor_id' => $mentorUsers[1]->id,
            'competition_name' => 'Gemastik XV UI/UX',
            'student_name' => 'Tim VJ Innovate',
            'result' => 'Finalis Nasional',
            'year' => 2023,
        ]);

        unset($admin, $pimpinan);
    }

    /**
     * Create a user and set their role via property assignment because role
     * is intentionally not mass-assignable on the User model.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function createUser(array $attributes, string $role): User
    {
        $user = User::create($attributes);
        $user->role = $role;
        $user->save();

        return $user;
    }
}
