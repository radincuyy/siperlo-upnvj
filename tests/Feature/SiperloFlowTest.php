<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FundRequest;
use App\Models\Mentor;
use App\Models\MentorRequest;
use App\Models\Registration;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiperloFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_mahasiswa_can_view_and_register_competition(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('email', 'mahasiswa@siperlo.test')->firstOrFail();
        $competition = Competition::where('status', 'open')->firstOrFail();

        $this->actingAs($student)
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertSee('Daftar Lomba');

        $this->actingAs($student)
            ->post(route('competitions.register', $competition))
            ->assertRedirect(route('registrations.index'));

        $this->actingAs($student)
            ->get(route('registrations.index'))
            ->assertOk()
            ->assertSee('Lomba Saya');
    }

    public function test_admin_dashboard_and_access_control_work(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $student = User::where('email', 'mahasiswa@siperlo.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Admin');

        $this->actingAs($student)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_create_competition_with_detail_assets(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $student = User::where('email', 'mahasiswa@siperlo.test')->firstOrFail();

        $payload = [
            'title' => 'UI UX Challenge Nasional',
            'description' => 'Kompetisi rancangan produk digital untuk mahasiswa.',
            'organizer' => 'Asosiasi Digital Indonesia',
            'category' => 'Teknologi',
            'type' => 'Nasional',
            'registration_deadline' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'event_start' => now()->addDays(20)->format('Y-m-d H:i:s'),
            'event_end' => now()->addDays(21)->format('Y-m-d H:i:s'),
            'location' => 'Online',
            'fee' => 0,
            'poster_image' => UploadedFile::fake()->create('poster.webp', 128, 'image/webp'),
            'guidebook_file' => UploadedFile::fake()->create('guidebook.pdf', 128, 'application/pdf'),
            'contact_person_name' => 'Panitia UI UX',
            'contact_person_phone' => '081234567890',
            'contact_person_email' => 'panitia@example.com',
            'official_website' => 'https://example.com',
            'social_media' => 'https://instagram.com/example',
            'external_registration_url' => 'https://example.com/register',
            'requirements' => "Mahasiswa aktif\nTim 2-3 orang",
            'benefits' => "Sertifikat\nSKPI",
            'timeline' => "10 Mei - Deadline\n20 Mei - Final",
            'status' => 'open',
        ];

        $this->actingAs($admin)
            ->post(route('admin.competitions.store'), $payload)
            ->assertRedirect(route('admin.competitions.index'));

        $competition = Competition::where('title', 'UI UX Challenge Nasional')->firstOrFail();

        Storage::disk('public')->assertExists($competition->poster_image);
        Storage::disk('public')->assertExists($competition->guidebook_file);

        $this->actingAs($student)
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Lihat Guidebook')
            ->assertSee('Contact Person')
            ->assertSee('Panitia UI UX')
            ->assertSee('Sertifikat');
    }

    public function test_open_competition_after_deadline_cannot_be_registered(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('email', 'mahasiswa@siperlo.test')->firstOrFail();
        $competition = Competition::create([
            'title' => 'Lomba Expired',
            'description' => 'Pendaftaran seharusnya sudah tertutup.',
            'organizer' => 'Panitia Demo',
            'category' => 'Akademik',
            'type' => 'Nasional',
            'registration_deadline' => now()->subDay(),
            'status' => 'open',
        ]);

        $this->actingAs($student)
            ->post(route('competitions.register', $competition))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('registrations', [
            'user_id' => $student->id,
            'competition_id' => $competition->id,
        ]);
    }

    public function test_mentor_search_excludes_inactive_mentor_by_name(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('email', 'mahasiswa@siperlo.test')->firstOrFail();
        $inactiveMentor = Mentor::whereHas('user', fn ($query) => $query->where('name', 'Dr. Ridwan Arif'))->firstOrFail();
        $inactiveMentor->update(['is_active' => false]);

        $this->actingAs($student)
            ->get(route('mentors.index', ['search' => 'Ridwan']))
            ->assertOk()
            ->assertDontSee('Dr. Ridwan Arif');
    }

    public function test_approved_mentor_request_cannot_be_reviewed_again(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $mentorRequest = MentorRequest::where('status', 'pending')->firstOrFail();
        $initialTotal = $mentorRequest->mentor->total_mentored;
        $initialRegistrationStatus = $mentorRequest->registration->status;

        $payload = [
            'status' => 'approved',
            'admin_notes' => 'Disetujui untuk demo.',
        ];

        $this->actingAs($admin)
            ->patch(route('admin.mentor-requests.update', $mentorRequest), $payload)
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->patch(route('admin.mentor-requests.update', $mentorRequest->fresh()), [
                'status' => 'rejected',
                'admin_notes' => 'Mencoba mengubah keputusan final.',
            ])
            ->assertSessionHas('error');

        $this->assertSame($initialTotal + 1, $mentorRequest->mentor->fresh()->total_mentored);
        $this->assertSame('approved', $mentorRequest->fresh()->status);
        $this->assertSame($initialRegistrationStatus, $mentorRequest->registration->fresh()->status);
    }

    public function test_admin_cannot_approve_new_mentor_request_when_registration_already_has_approved_mentor(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $existingApproved = MentorRequest::where('status', 'approved')->firstOrFail();
        $registration = $existingApproved->registration;
        $oldMentor = $existingApproved->mentor;
        $newMentor = Mentor::where('id', '!=', $oldMentor->id)->firstOrFail();
        $pendingRequest = MentorRequest::create([
            'user_id' => $registration->user_id,
            'mentor_id' => $newMentor->id,
            'registration_id' => $registration->id,
            'reason' => 'Mengganti mentor agar sesuai kebutuhan terbaru.',
            'status' => 'pending',
        ]);

        $oldMentorTotal = $oldMentor->total_mentored;
        $newMentorTotal = $newMentor->total_mentored;

        $this->actingAs($admin)
            ->patch(route('admin.mentor-requests.update', $pendingRequest), [
                'status' => 'approved',
                'admin_notes' => 'Mentor baru disetujui.',
            ])
            ->assertSessionHas('error');

        $this->assertSame('approved', $existingApproved->fresh()->status);
        $this->assertSame('pending', $pendingRequest->fresh()->status);
        $this->assertSame($oldMentor->id, $registration->fresh()->mentor_id);
        $this->assertSame($oldMentorTotal, $oldMentor->fresh()->total_mentored);
        $this->assertSame($newMentorTotal, $newMentor->fresh()->total_mentored);
        $this->assertSame(1, MentorRequest::where('registration_id', $registration->id)->where('status', 'approved')->count());
    }

    public function test_admin_cannot_approve_mentor_request_after_registration_finished(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $mentorRequest = MentorRequest::where('status', 'pending')->firstOrFail();
        $mentorTotal = $mentorRequest->mentor->total_mentored;
        $mentorRequest->registration->update(['status' => 'finished']);

        $this->actingAs($admin)
            ->patch(route('admin.mentor-requests.update', $mentorRequest), [
                'status' => 'approved',
                'admin_notes' => 'Mencoba approve setelah selesai.',
            ])
            ->assertSessionHas('error');

        $this->assertSame('pending', $mentorRequest->fresh()->status);
        $this->assertSame($mentorTotal, $mentorRequest->mentor->fresh()->total_mentored);
        $this->assertNull($mentorRequest->registration->fresh()->mentor_id);
    }

    public function test_admin_can_filter_mentor_requests_by_status(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $pendingRequest = MentorRequest::where('status', 'pending')->firstOrFail();
        $approvedRequest = MentorRequest::where('status', 'approved')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.mentor-requests.index'))
            ->assertOk()
            ->assertSee($pendingRequest->reason)
            ->assertDontSee($approvedRequest->reason)
            ->assertDontSee('Revisi');

        $this->actingAs($admin)
            ->get(route('admin.mentor-requests.index', ['status' => 'approved']))
            ->assertOk()
            ->assertSee($approvedRequest->reason)
            ->assertDontSee($pendingRequest->reason)
            ->assertSee('Keputusan review sudah final.')
            ->assertDontSee('Update Review')
            ->assertDontSee('<select id="mentor-status', false);
    }

    public function test_approved_fund_request_is_final_and_does_not_change_main_registration_status(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $fundRequest = FundRequest::where('status', 'pending')->firstOrFail();
        $initialRegistrationStatus = $fundRequest->registration->status;

        $this->actingAs($admin)
            ->patch(route('admin.fund-requests.update', $fundRequest), [
                'status' => 'approved',
                'admin_notes' => 'Dana disetujui.',
            ])
            ->assertSessionHas('success');

        $this->assertSame('approved', $fundRequest->fresh()->status);
        $this->assertSame($initialRegistrationStatus, $fundRequest->registration->fresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.fund-requests.update', $fundRequest->fresh()), [
                'status' => 'rejected',
                'admin_notes' => 'Mencoba mengubah keputusan final.',
            ])
            ->assertSessionHas('error');

        $this->assertSame('approved', $fundRequest->fresh()->status);
        $this->assertSame($initialRegistrationStatus, $fundRequest->registration->fresh()->status);
    }

    public function test_admin_cannot_approve_fund_request_after_registration_finished(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $fundRequest = FundRequest::where('status', 'pending')->firstOrFail();
        $fundRequest->registration->update(['status' => 'finished']);

        $this->actingAs($admin)
            ->patch(route('admin.fund-requests.update', $fundRequest), [
                'status' => 'approved',
                'admin_notes' => 'Mencoba approve setelah selesai.',
            ])
            ->assertSessionHas('error');

        $this->assertSame('pending', $fundRequest->fresh()->status);
    }

    public function test_admin_cannot_approve_second_fund_request_for_same_registration(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $student = User::where('email', 'mahasiswa@siperlo.test')->firstOrFail();
        $registration = Registration::where('user_id', $student->id)->firstOrFail();
        $approvedRequest = FundRequest::create([
            'user_id' => $student->id,
            'registration_id' => $registration->id,
            'amount' => 750000,
            'purpose' => 'Dana yang sudah disetujui',
            'description' => 'Pengajuan pertama sudah final.',
            'status' => 'approved',
        ]);
        $pendingRequest = FundRequest::create([
            'user_id' => $student->id,
            'registration_id' => $registration->id,
            'amount' => 500000,
            'purpose' => 'Dana duplikat',
            'description' => 'Pengajuan kedua tidak boleh ikut disetujui.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.fund-requests.update', $pendingRequest), [
                'status' => 'approved',
                'admin_notes' => 'Mencoba approve dana kedua.',
            ])
            ->assertSessionHas('error');

        $this->assertSame('approved', $approvedRequest->fresh()->status);
        $this->assertSame('pending', $pendingRequest->fresh()->status);
        $this->assertSame(1, FundRequest::where('registration_id', $registration->id)->where('status', 'approved')->count());
    }

    public function test_admin_can_filter_fund_requests_by_status(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $student = User::where('email', 'mahasiswa@siperlo.test')->firstOrFail();
        $registration = Registration::where('user_id', $student->id)->firstOrFail();
        $pendingRequest = FundRequest::where('status', 'pending')->firstOrFail();
        $approvedRequest = FundRequest::create([
            'user_id' => $student->id,
            'registration_id' => $registration->id,
            'amount' => 750000,
            'purpose' => 'Biaya arsip dana disetujui',
            'description' => 'Data pembanding untuk filter status dana.',
            'status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.fund-requests.index'))
            ->assertOk()
            ->assertSee($pendingRequest->purpose)
            ->assertDontSee($approvedRequest->purpose)
            ->assertDontSee('Revisi');

        $this->actingAs($admin)
            ->get(route('admin.fund-requests.index', ['status' => 'approved']))
            ->assertOk()
            ->assertSee($approvedRequest->purpose)
            ->assertDontSee($pendingRequest->purpose)
            ->assertSee('Keputusan review sudah final.')
            ->assertDontSee('Update Review')
            ->assertDontSee('<select id="fund-status', false);
    }

    public function test_student_cannot_submit_duplicate_active_mentor_or_fund_requests(): void
    {
        $this->seed(DatabaseSeeder::class);

        $studentWithMentor = User::where('email', 'mahasiswa@siperlo.test')->firstOrFail();
        $mentor = Mentor::whereDoesntHave('requests', fn ($query) => $query->where('user_id', $studentWithMentor->id))->firstOrFail();
        $mentorRegistration = Registration::where('user_id', $studentWithMentor->id)->firstOrFail();

        $this->actingAs($studentWithMentor)
            ->get(route('mentors.show', $mentor))
            ->assertOk()
            ->assertSee('Tidak ada lomba aktif yang bisa diajukan mentor.')
            ->assertDontSee($mentorRegistration->competition->title);

        $mentorRequestCount = MentorRequest::count();

        $this->actingAs($studentWithMentor)
            ->post(route('mentor-requests.store'), [
                'registration_id' => $mentorRegistration->id,
                'mentor_id' => $mentor->id,
                'reason' => 'Mencoba pengajuan mentor duplikat.',
            ])
            ->assertRedirect(route('registrations.index'))
            ->assertSessionHas('error');

        $this->assertSame($mentorRequestCount, MentorRequest::count());

        $studentWithFund = User::where('email', 'nadia@siperlo.test')->firstOrFail();
        $fundRegistration = Registration::where('user_id', $studentWithFund->id)->firstOrFail();

        $this->actingAs($studentWithFund)
            ->get(route('fund-requests.create'))
            ->assertOk()
            ->assertSee('Tidak ada lomba aktif yang bisa diajukan bantuan dana.')
            ->assertDontSee($fundRegistration->competition->title);

        $fundRequestCount = FundRequest::count();

        $this->actingAs($studentWithFund)
            ->post(route('fund-requests.store'), [
                'registration_id' => $fundRegistration->id,
                'amount' => 500000,
                'purpose' => 'Mencoba pengajuan dana duplikat',
                'description' => 'Pengajuan aktif sudah ada.',
            ])
            ->assertRedirect(route('registrations.index'))
            ->assertSessionHas('error');

        $this->assertSame($fundRequestCount, FundRequest::count());
    }

    public function test_admin_can_filter_registrations_by_status(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $registered = Registration::where('status', 'registered')->firstOrFail();
        $ongoing = Registration::where('id', '!=', $registered->id)->firstOrFail();
        $ongoing->update(['status' => 'ongoing']);

        $this->actingAs($admin)
            ->get(route('admin.registrations.index', ['status' => 'registered']))
            ->assertOk()
            ->assertSee($registered->competition->title)
            ->assertDontSee($ongoing->competition->title);

        $this->actingAs($admin)
            ->get(route('admin.registrations.index', ['status' => 'ongoing']))
            ->assertOk()
            ->assertSee($ongoing->competition->title)
            ->assertDontSee($registered->competition->title)
            ->assertSee('Menunggu mahasiswa mengirim laporan hasil. Status utama dikunci setelah lomba berjalan.')
            ->assertDontSee('<option value="registered"', false);

        $this->actingAs($admin)
            ->patch(route('admin.registrations.update', $ongoing), [
                'status' => 'registered',
                'notes' => 'Mencoba mundur ke terdaftar.',
            ])
            ->assertSessionHas('error');

        $this->assertSame('ongoing', $ongoing->fresh()->status);
    }

    public function test_admin_cannot_finish_registration_without_result_report(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $registration = Registration::whereNull('result_status')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.registrations.index', ['status' => 'registered']))
            ->assertOk()
            ->assertSee('Belum ada laporan hasil yang bisa direview.')
            ->assertDontSee('Status laporan hasil');

        $this->actingAs($admin)
            ->patch(route('admin.registrations.update', $registration), [
                'status' => 'finished',
                'notes' => 'Mencoba menyelesaikan tanpa laporan.',
            ])
            ->assertSessionHas('error');

        $this->assertNotSame('finished', $registration->fresh()->status);
    }

    public function test_approved_result_registration_cannot_be_moved_back_to_ongoing(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $registration = Registration::firstOrFail();
        $registration->update([
            'status' => 'finished',
            'result' => 'Finalis Nasional',
            'result_status' => 'approved',
            'result_submitted_at' => now(),
            'result_reviewed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.registrations.update', $registration), [
                'status' => 'ongoing',
                'result' => 'Finalis Nasional',
                'result_status' => 'approved',
                'result_admin_notes' => 'Sudah valid.',
                'notes' => 'Coba mundurkan status.',
            ])
            ->assertSessionHas('success');

        $registration->refresh();

        $this->assertSame('finished', $registration->status);
        $this->assertSame('approved', $registration->result_status);

        $this->actingAs($admin)
            ->get(route('admin.registrations.index', ['status' => 'ongoing']))
            ->assertOk()
            ->assertDontSee($registration->competition->title);
    }

    public function test_approved_result_is_treated_as_finished_even_if_stored_status_is_ongoing(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $student = User::where('email', 'mahasiswa@siperlo.test')->firstOrFail();
        $competition = Competition::create([
            'title' => 'Regression Approved Result State',
            'description' => 'Data khusus untuk memastikan laporan disetujui tidak tampil sebagai berlangsung.',
            'organizer' => 'Panitia Regression',
            'category' => 'Teknologi',
            'type' => 'Nasional',
            'registration_deadline' => now()->addDays(7),
            'status' => 'open',
        ]);
        $registration = Registration::create([
            'user_id' => $student->id,
            'competition_id' => $competition->id,
            'status' => 'ongoing',
            'result' => 'Tidak lolos babak final',
            'result_status' => 'approved',
            'result_submitted_at' => now(),
            'result_reviewed_at' => now(),
        ]);

        $this->assertSame('finished', $registration->fresh()->primaryStatus());

        $this->actingAs($admin)
            ->get(route('admin.registrations.index', ['status' => 'ongoing']))
            ->assertOk()
            ->assertDontSee($competition->title);

        $this->actingAs($admin)
            ->get(route('admin.registrations.index', ['status' => 'finished']))
            ->assertOk()
            ->assertSee($competition->title)
            ->assertSee('Selesai');

        $this->actingAs($admin)
            ->patch(route('admin.registrations.update', $registration), [
                'status' => 'ongoing',
                'result' => 'Tidak lolos babak final',
                'result_status' => 'approved',
                'result_admin_notes' => 'Bukti valid.',
                'notes' => 'Mencoba menyimpan sebagai berlangsung.',
            ])
            ->assertSessionHas('success');

        $this->assertSame('finished', $registration->fresh()->status);
    }

    public function test_student_reports_result_after_ongoing_and_admin_approves_it(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $student = User::where('email', 'mahasiswa@siperlo.test')->firstOrFail();
        $registration = Registration::where('user_id', $student->id)->firstOrFail();

        $this->actingAs($student)
            ->get(route('registrations.results.create', $registration))
            ->assertRedirect(route('registrations.index'))
            ->assertSessionHas('error');

        $registration->update(['status' => 'ongoing']);

        $this->actingAs($student)
            ->post(route('registrations.results.store', $registration), [
                'result' => 'Finalis Nasional',
                'result_description' => 'Tim berhasil masuk tahap final nasional.',
                'result_proof_file' => UploadedFile::fake()->create('sertifikat.pdf', 128, 'application/pdf'),
            ])
            ->assertRedirect(route('registrations.index'))
            ->assertSessionHas('success');

        $registration->refresh();

        $this->assertSame('ongoing', $registration->status);
        $this->assertSame('pending', $registration->result_status);
        Storage::disk('public')->assertExists($registration->result_proof_file);

        $this->actingAs($admin)
            ->get(route('admin.registrations.index', ['status' => 'ongoing']))
            ->assertOk()
            ->assertDontSee($registration->competition->title)
            ->assertDontSee('Status laporan hasil');

        $this->actingAs($admin)
            ->get(route('admin.registrations.index', ['status' => 'result_pending']))
            ->assertOk()
            ->assertSee($registration->competition->title)
            ->assertSee('Status laporan hasil')
            ->assertSee('Status utama akan otomatis mengikuti keputusan validasi laporan hasil.')
            ->assertDontSee('<option value="registered"', false)
            ->assertDontSee('<option value="finished"', false);

        $this->actingAs($admin)
            ->patch(route('admin.registrations.update', $registration), [
                'status' => 'finished',
                'result' => 'Finalis Nasional',
                'result_status' => 'pending',
                'result_admin_notes' => 'Masih ditahan untuk review.',
                'notes' => 'Request manual mencoba memaksa selesai.',
            ])
            ->assertSessionHas('success');

        $registration->refresh();

        $this->assertSame('ongoing', $registration->status);
        $this->assertSame('pending', $registration->result_status);

        $this->actingAs($admin)
            ->patch(route('admin.registrations.update', $registration), [
                'status' => 'ongoing',
                'result' => 'Finalis Nasional',
                'result_status' => 'approved',
                'result_admin_notes' => 'Bukti valid.',
                'notes' => 'Hasil sudah divalidasi.',
            ])
            ->assertSessionHas('success');

        $registration->refresh();

        $this->assertSame('finished', $registration->status);
        $this->assertSame('approved', $registration->result_status);
        $this->assertNotNull($registration->result_reviewed_at);
    }

    public function test_result_revision_keeps_registration_ongoing_and_allows_student_to_resubmit(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Mahasiswa Revisi Laporan',
        ]);
        $competition = Competition::create([
            'title' => 'Revision Result Flow Challenge',
            'description' => 'Data khusus untuk memastikan revisi laporan bisa dikirim ulang.',
            'organizer' => 'Panitia Revision',
            'category' => 'Teknologi',
            'type' => 'Nasional',
            'registration_deadline' => now()->addDays(7),
            'status' => 'open',
        ]);
        $registration = Registration::create([
            'user_id' => $student->id,
            'competition_id' => $competition->id,
            'status' => 'ongoing',
            'result' => 'Finalis',
            'result_status' => 'pending',
            'result_submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.registrations.update', $registration), [
                'status' => 'ongoing',
                'result' => 'Finalis',
                'result_status' => 'revision',
                'result_admin_notes' => 'Bukti perlu diperjelas.',
                'notes' => 'Mahasiswa perlu memperbarui laporan.',
            ])
            ->assertSessionHas('success');

        $registration->refresh();

        $this->assertSame('ongoing', $registration->status);
        $this->assertSame('revision', $registration->result_status);
        $this->assertNotNull($registration->result_reviewed_at);

        $this->actingAs($admin)
            ->get(route('admin.registrations.index', [
                'status' => 'ongoing',
                'search' => 'Revision Result Flow Challenge',
            ]))
            ->assertOk()
            ->assertSee($competition->title)
            ->assertSee('Laporan perlu revisi. Mahasiswa harus memperbarui laporan hasil sebelum proses bisa difinalkan.')
            ->assertDontSee('<option value="finished"', false);

        $this->actingAs($admin)
            ->get(route('admin.registrations.index', ['status' => 'result_pending']))
            ->assertOk()
            ->assertDontSee($competition->title);

        $this->actingAs($admin)
            ->patch(route('admin.registrations.update', $registration), [
                'status' => 'registered',
                'notes' => 'Mencoba mengembalikan ke terdaftar.',
            ])
            ->assertSessionHas('error');

        $this->assertSame('ongoing', $registration->fresh()->status);

        $this->actingAs($student)
            ->get(route('registrations.index'))
            ->assertOk()
            ->assertSee('Perlu Tindakan')
            ->assertSee('Catatan perbaikan:')
            ->assertSee('Perbarui Laporan Hasil')
            ->assertDontSee('Hasil Dilaporkan')
            ->assertDontSee('Cari Mentor Opsional')
            ->assertDontSee('Ajukan Dana Opsional');

        $this->actingAs($student)
            ->get(route('registrations.results.create', $registration))
            ->assertOk()
            ->assertSee('Bukti perlu diperjelas.');

        $this->actingAs($student)
            ->post(route('registrations.results.store', $registration), [
                'result' => 'Finalis Nasional',
                'result_description' => 'Laporan revisi dengan bukti yang lebih jelas.',
                'result_proof_file' => UploadedFile::fake()->create('bukti-revisi.pdf', 128, 'application/pdf'),
            ])
            ->assertRedirect(route('registrations.index'))
            ->assertSessionHas('success');

        $registration->refresh();

        $this->assertSame('ongoing', $registration->status);
        $this->assertSame('pending', $registration->result_status);
        $this->assertNull($registration->result_reviewed_at);
        $this->assertNull($registration->result_admin_notes);
    }

    public function test_rejected_result_is_final_and_moves_registration_to_finished(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@siperlo.test')->firstOrFail();
        $student = User::where('email', 'mahasiswa@siperlo.test')->firstOrFail();
        $competition = Competition::create([
            'title' => 'Rejected Result Final Flow Challenge',
            'description' => 'Data khusus untuk memastikan laporan ditolak menjadi final.',
            'organizer' => 'Panitia Rejected',
            'category' => 'Akademik',
            'type' => 'Nasional',
            'registration_deadline' => now()->addDays(7),
            'status' => 'open',
        ]);
        $registration = Registration::create([
            'user_id' => $student->id,
            'competition_id' => $competition->id,
            'status' => 'ongoing',
            'result' => 'Juara 3',
            'result_status' => 'pending',
            'result_submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.registrations.update', $registration), [
                'status' => 'ongoing',
                'result' => 'Juara 3',
                'result_status' => 'rejected',
                'result_admin_notes' => 'Bukti tidak sesuai dengan lomba.',
                'notes' => 'Laporan ditutup karena tidak valid.',
            ])
            ->assertSessionHas('success');

        $registration->refresh();

        $this->assertSame('finished', $registration->status);
        $this->assertSame('finished', $registration->primaryStatus());
        $this->assertSame('rejected', $registration->result_status);
        $this->assertNotNull($registration->result_reviewed_at);
        $this->assertFalse($registration->canReportResult());

        $this->actingAs($admin)
            ->get(route('admin.registrations.index', ['status' => 'ongoing']))
            ->assertOk()
            ->assertDontSee($competition->title);

        $this->actingAs($admin)
            ->get(route('admin.registrations.index', ['status' => 'finished']))
            ->assertOk()
            ->assertSee($competition->title)
            ->assertSee('Ditolak');

        $this->actingAs($student)
            ->get(route('registrations.results.create', $registration))
            ->assertRedirect(route('registrations.index'))
            ->assertSessionHas('error');
    }

    public function test_finished_registration_cannot_request_optional_mentor_or_fund(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('email', 'mahasiswa@siperlo.test')->firstOrFail();
        $mentor = Mentor::where('is_active', true)->firstOrFail();
        $registration = Registration::with('competition')
            ->where('user_id', $student->id)
            ->firstOrFail();

        $registration->update(['status' => 'finished']);

        $this->actingAs($student)
            ->get(route('mentors.show', $mentor))
            ->assertOk()
            ->assertSee('Tidak ada lomba aktif yang bisa diajukan mentor.')
            ->assertDontSee($registration->competition->title);

        $this->actingAs($student)
            ->get(route('fund-requests.create'))
            ->assertOk()
            ->assertSee('Tidak ada lomba aktif yang bisa diajukan bantuan dana.')
            ->assertDontSee($registration->competition->title);

        $mentorRequestCount = MentorRequest::count();
        $fundRequestCount = FundRequest::count();

        $this->actingAs($student)
            ->post(route('mentor-requests.store'), [
                'registration_id' => $registration->id,
                'mentor_id' => $mentor->id,
                'reason' => 'Butuh diskusi lanjutan.',
            ])
            ->assertRedirect(route('registrations.index'))
            ->assertSessionHas('error');

        $this->assertSame($mentorRequestCount, MentorRequest::count());

        $this->actingAs($student)
            ->post(route('fund-requests.store'), [
                'registration_id' => $registration->id,
                'amount' => 500000,
                'purpose' => 'Biaya tambahan',
                'description' => 'Pengajuan setelah selesai seharusnya ditolak.',
            ])
            ->assertRedirect(route('registrations.index'))
            ->assertSessionHas('error');

        $this->assertSame($fundRequestCount, FundRequest::count());
    }
}
