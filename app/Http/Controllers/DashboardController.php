<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\FundRequest;
use App\Models\Mentor;
use App\Models\MentorRequest;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->dashboardRoute());
    }

    public function admin(): View
    {
        return view('dashboard.monitoring', [
            'title' => 'Dashboard Admin',
            'readOnly' => false,
            ...$this->dashboardData(),
        ]);
    }

    public function pimpinan(): View
    {
        return view('dashboard.monitoring', [
            'title' => 'Dashboard Pimpinan',
            'readOnly' => true,
            ...$this->dashboardData(),
        ]);
    }

    public function mentor(Request $request): View
    {
        $mentor = $request->user()->mentor()->with('achievements')->first();

        return view('dashboard.mentor', [
            'mentor' => $mentor,
            'requests' => $mentor
                ? $mentor->requests()->with(['user', 'registration.competition'])->latest()->get()
                : collect(),
            'registrations' => $mentor
                ? $mentor->registrations()->with(['user', 'competition'])->latest()->get()
                : collect(),
        ]);
    }

    private function dashboardData(): array
    {
        return [
            'stats' => [
                'competitions' => Competition::count(),
                'registrations' => Registration::count(),
                'mentorPending' => MentorRequest::where('status', 'pending')->count(),
                'fundPending' => FundRequest::where('status', 'pending')->count(),
                'finished' => Registration::where('status', 'finished')->count(),
                'mentors' => Mentor::where('is_active', true)->count(),
            ],
            'registrationsByStatus' => [
                'registered' => Registration::registeredTab()->count(),
                'ongoing' => Registration::ongoingTab()->count(),
                'finished' => Registration::finishedTab()->count(),
            ],
            'recentRegistrations' => Registration::with(['user', 'competition', 'mentor.user', 'latestFundRequest'])
                ->latest()
                ->take(8)
                ->get(),
            'recentFunds' => FundRequest::with(['user', 'registration.competition'])
                ->latest()
                ->take(6)
                ->get(),
        ];
    }
}
