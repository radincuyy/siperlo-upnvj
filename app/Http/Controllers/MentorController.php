<?php

namespace App\Http\Controllers;

use App\Models\Mentor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MentorController extends Controller
{
    public function index(Request $request): View
    {
        $mentors = Mentor::with(['user', 'achievements'])
            ->where('is_active', true)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($query) use ($search) {
                    $query->where('expertise', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('total_mentored')
            ->paginate(9)
            ->withQueryString();

        return view('mentors.index', compact('mentors'));
    }

    public function show(Request $request, Mentor $mentor): View
    {
        return view('mentors.show', [
            'mentor' => $mentor->load(['user', 'achievements', 'registrations.competition']),
            'availableRegistrations' => $request->user()?->isRole('mahasiswa')
                ? $request->user()
                    ->registrations()
                    ->with(['competition', 'mentorRequests'])
                    ->latest()
                    ->get()
                    ->filter(fn ($registration) => $registration->canRequestMentor())
                    ->values()
                : collect(),
        ]);
    }
}
