<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\FundRequest;
use App\Models\Mentor;
use App\Models\MentorRequest;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            'chartByCategory' => $this->chartByCategory(),
            'chartTrend' => $this->chartTrend(),
            'chartResults' => $this->chartResults(),
        ];
    }

    /**
     * Pendaftaran per kategori lomba.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    private function chartByCategory(): array
    {
        $rows = Registration::query()
            ->join('competitions', 'competitions.id', '=', 'registrations.competition_id')
            ->selectRaw('competitions.category as category, COUNT(*) as total')
            ->groupBy('competitions.category')
            ->orderByDesc('total')
            ->pluck('total', 'category');

        return [
            'labels' => array_map(fn ($v) => (string) $v, array_keys($rows->all())),
            'values' => array_map(fn ($v) => (int) $v, array_values($rows->all())),
        ];
    }

    /**
     * Tren pendaftaran 6 bulan terakhir (termasuk bulan ini).
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    private function chartTrend(): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths(5);

        $rows = Registration::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn ($r) => $r->created_at->format('Y-m'))
            ->map(fn ($group) => $group->count());

        $labels = [];
        $values = [];

        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->translatedFormat('M Y');
            $values[] = (int) ($rows[$key] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Sebaran hasil lomba berdasarkan result_status.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    private function chartResults(): array
    {
        $counts = [
            'pending' => 0,
            'approved' => 0,
            'revision' => 0,
            'rejected' => 0,
            'belum' => 0,
        ];

        $rows = Registration::query()
            ->selectRaw('result_status, COUNT(*) as total')
            ->groupBy('result_status')
            ->pluck('total', 'result_status');

        foreach ($rows as $key => $total) {
            $bucket = $key === null || $key === '' ? 'belum' : $key;
            $counts[$bucket] = ($counts[$bucket] ?? 0) + (int) $total;
        }

        $labelMap = Registration::RESULT_STATUSES + ['belum' => 'Belum Dilaporkan'];

        $labels = [];
        $values = [];

        foreach ($counts as $key => $value) {
            $labels[] = $labelMap[$key] ?? ucfirst($key);
            $values[] = $value;
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
