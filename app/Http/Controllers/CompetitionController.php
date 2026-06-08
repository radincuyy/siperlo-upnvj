<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function index(Request $request): View
    {
        $competitions = Competition::query()
            ->visible()
            ->withCount('registrations')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('organizer', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category') && $request->category !== 'all', fn ($query) => $query->where('category', $request->category))
            ->when($request->filled('type') && $request->type !== 'all', fn ($query) => $query->where('type', $request->type))
            ->orderByRaw("CASE WHEN registration_deadline >= ? THEN 0 ELSE 1 END", [now()->toDateTimeString()])
            ->orderBy('registration_deadline')
            ->paginate(8)
            ->withQueryString();

        return view('competitions.index', [
            'competitions' => $competitions,
            'upcomingDeadlines' => Competition::query()
                ->visible()
                ->where('registration_deadline', '>=', now())
                ->orderBy('registration_deadline')
                ->limit(3)
                ->get(),
            'categories' => array_keys(Competition::CATEGORIES),
            'types' => array_keys(Competition::TYPES),
            'registeredCompetitionIds' => $request->user()?->registrations()->pluck('competition_id')->all() ?? [],
        ]);
    }

    public function show(Request $request, Competition $competition): View
    {
        $competition->loadCount('registrations');

        return view('competitions.show', [
            'competition' => $competition,
            'registration' => $request->user()
                ? Registration::with(['mentor.user', 'mentorRequests.mentor.user', 'latestFundRequest'])
                    ->where('user_id', $request->user()->id)
                    ->where('competition_id', $competition->id)
                    ->first()
                : null,
        ]);
    }

    public function register(Request $request, Competition $competition): RedirectResponse
    {
        if ($competition->status !== 'open' || $competition->registration_deadline->isPast()) {
            return back()->with('error', 'Pendaftaran lomba ini belum tersedia atau sudah ditutup.');
        }

        $existing = Registration::where('user_id', $request->user()->id)
            ->where('competition_id', $competition->id)
            ->first();

        if ($existing) {
            return redirect()
                ->route('registrations.index')
                ->with('info', "Kamu sudah terdaftar di lomba {$competition->title}.");
        }

        $request->validate([
            'registration_proof_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'registration_proof_file.required' => 'Bukti pendaftaran wajib diupload.',
            'registration_proof_file.mimes' => 'Format file harus JPG, PNG, atau PDF.',
            'registration_proof_file.max' => 'Ukuran file maksimal 2MB.',
        ]);

        $proofPath = $request->file('registration_proof_file')
            ->store('registration-proofs', 'public');

        try {
            $registration = Registration::create([
                'user_id' => $request->user()->id,
                'competition_id' => $competition->id,
                'status' => 'registered',
                'registration_proof_file' => $proofPath,
                'proof_status' => 'pending',
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($proofPath);

            throw $exception;
        }

        return redirect()
            ->route('registrations.index')
            ->with('success', "Pendaftaran {$registration->competition->title} berhasil. Bukti pendaftaran sedang menunggu verifikasi admin.");
    }

    public function reuploadProof(Request $request, Registration $registration): RedirectResponse
    {
        if ((int) $registration->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        if (! $registration->canUploadProof()) {
            return back()->with('error', 'Bukti pendaftaran tidak bisa diupload ulang saat ini.');
        }

        $request->validate([
            'registration_proof_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'registration_proof_file.required' => 'Bukti pendaftaran wajib diupload.',
            'registration_proof_file.mimes' => 'Format file harus JPG, PNG, atau PDF.',
            'registration_proof_file.max' => 'Ukuran file maksimal 2MB.',
        ]);

        $oldProofPath = $registration->registration_proof_file;
        $proofPath = $request->file('registration_proof_file')
            ->store('registration-proofs', 'public');

        try {
            $registration->update([
                'registration_proof_file' => $proofPath,
                'proof_status' => 'pending',
                'proof_admin_notes' => null,
                'proof_verified_at' => null,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($proofPath);

            throw $exception;
        }

        if ($oldProofPath) {
            Storage::disk('public')->delete($oldProofPath);
        }

        return back()->with('success', 'Bukti pendaftaran berhasil diupload ulang. Menunggu verifikasi admin.');
    }

    public function my(Request $request): View
    {
        return view('registrations.index', [
            'registrations' => $request->user()
                ->registrations()
                ->with(['competition', 'mentor.user', 'latestFundRequest', 'fundRequests', 'mentorRequests.mentor.user'])
                ->latest()
                ->get(),
        ]);
    }
}
