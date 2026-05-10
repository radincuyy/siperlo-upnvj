<?php

namespace App\Http\Controllers;

use App\Models\Mentor;
use App\Models\MentorRequest;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MentorRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'registration_id' => ['required', 'exists:registrations,id'],
            'mentor_id' => ['required', 'exists:mentors,id'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $registration = Registration::where('user_id', $request->user()->id)
            ->findOrFail($data['registration_id']);

        if (! $registration->canRequestMentor()) {
            return redirect()
                ->route('registrations.index')
                ->with('error', 'Pengajuan mentor tidak tersedia untuk lomba ini.');
        }

        $mentor = Mentor::where('is_active', true)->findOrFail($data['mentor_id']);

        MentorRequest::create([
            'user_id' => $request->user()->id,
            'mentor_id' => $mentor->id,
            'registration_id' => $registration->id,
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('registrations.index')->with('success', 'Pengajuan mentor berhasil dikirim.');
    }
}
