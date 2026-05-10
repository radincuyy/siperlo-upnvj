<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FundRequest;
use App\Models\Mentor;
use App\Models\MentorRequest;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function registrations(Request $request): View
    {
        $statusTabs = [
            'all' => 'Semua',
            'registered' => 'Terdaftar',
            'ongoing' => 'Berlangsung',
            'result_pending' => 'Validasi Hasil',
            'finished' => 'Selesai',
        ];

        $selectedStatus = $request->string('status')->toString() ?: 'all';

        if (! array_key_exists($selectedStatus, $statusTabs)) {
            $selectedStatus = 'all';
        }

        $search = $request->string('search')->toString();
        $finalResultStatuses = Registration::FINAL_RESULT_STATUSES;

        $registrations = Registration::with(['user', 'competition', 'mentor.user', 'latestFundRequest'])
            ->when($selectedStatus === 'registered', fn ($query) => $query
                ->whereNotIn('status', ['ongoing', 'finished'])
                ->where(fn ($query) => $query->whereNull('result_status')->orWhereNotIn('result_status', $finalResultStatuses)))
            ->when($selectedStatus === 'ongoing', fn ($query) => $query
                ->where('status', 'ongoing')
                ->where(fn ($query) => $query->whereNull('result_status')->orWhere('result_status', 'revision')))
            ->when($selectedStatus === 'finished', fn ($query) => $query
                ->where(fn ($query) => $query->where('status', 'finished')->orWhereIn('result_status', $finalResultStatuses)))
            ->when($selectedStatus === 'result_pending', fn ($query) => $query->where('result_status', 'pending'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('result', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('competition', fn ($query) => $query->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('mentor.user', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw("CASE WHEN result_status = 'pending' THEN 0 WHEN status = 'registered' THEN 1 WHEN status = 'ongoing' THEN 2 WHEN status = 'finished' THEN 3 ELSE 4 END")
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $statusCounts = [
            'registered' => Registration::whereNotIn('status', ['ongoing', 'finished'])
                ->where(fn ($query) => $query->whereNull('result_status')->orWhereNotIn('result_status', $finalResultStatuses))
                ->count(),
            'ongoing' => Registration::where('status', 'ongoing')
                ->where(fn ($query) => $query->whereNull('result_status')->orWhere('result_status', 'revision'))
                ->count(),
            'result_pending' => Registration::where('result_status', 'pending')->count(),
            'finished' => Registration::where(fn ($query) => $query->where('status', 'finished')->orWhereIn('result_status', $finalResultStatuses))->count(),
        ];

        return view('admin.registrations.index', [
            'registrations' => $registrations,
            'statusTabs' => $statusTabs,
            'selectedStatus' => $selectedStatus,
            'statusCounts' => $statusCounts,
            'totalRegistrations' => Registration::count(),
            'search' => $search,
        ]);
    }

    public function updateRegistration(Request $request, Registration $registration): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:registered,ongoing,finished'],
            'result' => ['nullable', 'string', 'max:255'],
            'result_status' => ['nullable', 'in:pending,approved,rejected,revision'],
            'result_admin_notes' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! $registration->hasResultReport()) {
            if ($data['status'] === 'finished') {
                return back()
                    ->withInput()
                    ->with('error', 'Status Selesai membutuhkan laporan hasil dari mahasiswa yang sudah divalidasi.');
            }

            unset($data['result'], $data['result_status'], $data['result_admin_notes']);
        }

        $effectiveResultStatus = $data['result_status'] ?? $registration->result_status;
        $isReviewingResult = array_key_exists('result_status', $data);

        if (! $isReviewingResult && $registration->primaryStatus() === 'ongoing' && $data['status'] !== 'ongoing') {
            return back()
                ->withInput()
                ->with('error', 'Pendaftaran yang sudah Berlangsung tidak dapat dikembalikan ke Terdaftar atau diselesaikan manual. Selesaikan melalui validasi laporan hasil.');
        }

        if (! $isReviewingResult && $registration->hasFinalResult() && in_array($effectiveResultStatus, Registration::FINAL_RESULT_STATUSES, true) && $data['status'] !== 'finished') {
            return back()
                ->withInput()
                ->with('error', 'Pendaftaran dengan laporan hasil final harus tetap berstatus Selesai.');
        }

        if (! $isReviewingResult && $registration->hasResultReport() && $data['status'] === 'finished' && ! in_array($effectiveResultStatus, Registration::FINAL_RESULT_STATUSES, true)) {
            return back()
                ->withInput()
                ->with('error', 'Status Selesai hanya dapat dipilih jika laporan hasil disetujui atau ditolak final.');
        }

        if ($isReviewingResult) {
            if (in_array($data['result_status'], Registration::FINAL_RESULT_STATUSES, true)) {
                $data['status'] = 'finished';
                $data['result_reviewed_at'] = now();
            } elseif ($data['result_status'] === 'revision') {
                $data['status'] = 'ongoing';
                $data['result_reviewed_at'] = now();
            } elseif ($data['result_status'] === 'pending') {
                $data['status'] = 'ongoing';
                $data['result_reviewed_at'] = null;
            }
        }

        $registration->update($data);

        return back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }

    public function mentorRequests(Request $request): View
    {
        $statusTabs = ['all' => 'Semua', ...MentorRequest::REVIEW_STATUSES];
        $selectedStatus = $request->string('status')->toString() ?: 'pending';

        if (! array_key_exists($selectedStatus, $statusTabs)) {
            $selectedStatus = 'pending';
        }

        $search = $request->string('search')->toString();

        $requests = MentorRequest::with(['user', 'mentor.user', 'registration.competition'])
            ->when($selectedStatus !== 'all', fn ($query) => $query->where('status', $selectedStatus))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('mentor.user', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('registration.competition', fn ($query) => $query->where('title', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'revision' THEN 1 WHEN 'approved' THEN 2 ELSE 3 END")
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $statusCounts = MentorRequest::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.mentor_requests.index', [
            'requests' => $requests,
            'statusTabs' => $statusTabs,
            'selectedStatus' => $selectedStatus,
            'statusCounts' => $statusCounts,
            'totalRequests' => MentorRequest::count(),
            'search' => $search,
        ]);
    }

    public function updateMentorRequest(Request $request, MentorRequest $mentorRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($data, $mentorRequest) {
            $mentorRequest = MentorRequest::query()
                ->whereKey($mentorRequest->id)
                ->lockForUpdate()
                ->firstOrFail();
            $registration = Registration::query()
                ->whereKey($mentorRequest->registration_id)
                ->lockForUpdate()
                ->firstOrFail();
            $previousStatus = $mentorRequest->status;
            $previousMentorId = $registration->mentor_id;

            if ($previousStatus !== 'pending') {
                return back()
                    ->with('error', 'Keputusan review mentor sudah final dan tidak dapat diubah dari halaman ini.');
            }

            if ($data['status'] === 'approved' && ! $registration->canRequestOptionalSupport()) {
                return back()
                    ->withInput()
                    ->with('error', 'Pengajuan mentor tidak dapat disetujui karena lomba sudah selesai.');
            }

            if (
                $data['status'] === 'approved'
                && (
                    filled($registration->mentor_id)
                    || $registration->mentorRequests()
                        ->where('id', '!=', $mentorRequest->id)
                        ->where('status', 'approved')
                        ->exists()
                )
            ) {
                return back()
                    ->withInput()
                    ->with('error', 'Pendaftaran ini sudah memiliki mentor yang disetujui.');
            }

            $mentorRequest->update($data);

            if ($data['status'] === 'approved') {
                $registration->mentorRequests()
                    ->where('id', '!=', $mentorRequest->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'rejected',
                        'admin_notes' => 'Ditutup otomatis karena pengajuan mentor lain sudah disetujui.',
                    ]);

                $updates = [
                    'mentor_id' => $mentorRequest->mentor_id,
                ];

                if (in_array($registration->status, ['mentor_pending', 'mentored'], true)) {
                    $updates['status'] = 'registered';
                }

                $registration->update($updates);

                if ((int) $previousMentorId !== (int) $mentorRequest->mentor_id) {
                    $mentorRequest->mentor->increment('total_mentored');

                    if ($previousMentorId) {
                        Mentor::whereKey($previousMentorId)
                            ->where('total_mentored', '>', 0)
                            ->decrement('total_mentored');
                    }
                }
            }

            return back()->with('success', 'Review pengajuan mentor berhasil disimpan.');
        });
    }

    public function fundRequests(Request $request): View
    {
        $statusTabs = ['all' => 'Semua', ...FundRequest::REVIEW_STATUSES];
        $selectedStatus = $request->string('status')->toString() ?: 'pending';

        if (! array_key_exists($selectedStatus, $statusTabs)) {
            $selectedStatus = 'pending';
        }

        $search = $request->string('search')->toString();

        $requests = FundRequest::with(['user', 'registration.competition'])
            ->when($selectedStatus !== 'all', fn ($query) => $query->where('status', $selectedStatus))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('purpose', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('registration.competition', fn ($query) => $query->where('title', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'revision' THEN 1 WHEN 'approved' THEN 2 ELSE 3 END")
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $statusCounts = FundRequest::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.fund_requests.index', [
            'requests' => $requests,
            'statusTabs' => $statusTabs,
            'selectedStatus' => $selectedStatus,
            'statusCounts' => $statusCounts,
            'totalRequests' => FundRequest::count(),
            'search' => $search,
        ]);
    }

    public function updateFundRequest(Request $request, FundRequest $fundRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($data, $fundRequest) {
            $fundRequest = FundRequest::query()
                ->whereKey($fundRequest->id)
                ->lockForUpdate()
                ->firstOrFail();
            $registration = Registration::query()
                ->whereKey($fundRequest->registration_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($fundRequest->status !== 'pending') {
                return back()
                    ->with('error', 'Keputusan review dana sudah final dan tidak dapat diubah dari halaman ini.');
            }

            if ($data['status'] === 'approved' && ! $registration->canRequestOptionalSupport()) {
                return back()
                    ->withInput()
                    ->with('error', 'Pengajuan dana tidak dapat disetujui karena lomba sudah selesai.');
            }

            if (
                $data['status'] === 'approved'
                && $registration->fundRequests()
                    ->where('id', '!=', $fundRequest->id)
                    ->where('status', 'approved')
                    ->exists()
            ) {
                return back()
                    ->withInput()
                    ->with('error', 'Pendaftaran ini sudah memiliki pengajuan dana yang disetujui.');
            }

            $fundRequest->update($data);

            if (in_array($registration->status, ['fund_pending', 'funded'], true)) {
                $registration->update(['status' => 'registered']);
            }

            return back()->with('success', 'Review pengajuan dana berhasil disimpan.');
        });
    }
}
