<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $registration = Registration::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'competition_id' => $competition->id,
            ],
            ['status' => 'registered']
        );

        return redirect()
            ->route('registrations.index')
            ->with('success', "Pendaftaran {$registration->competition->title} berhasil dibuat.");
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
