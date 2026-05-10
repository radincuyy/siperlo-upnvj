<?php

namespace App\Http\Controllers;

use App\Models\FundRequest;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FundRequestController extends Controller
{
    public function create(Request $request): View
    {
        return view('fund_requests.create', [
            'registrations' => $request->user()
                ->registrations()
                ->with(['competition', 'fundRequests'])
                ->latest()
                ->get()
                ->filter(fn ($registration) => $registration->canRequestFund())
                ->values(),
            'selectedRegistrationId' => $request->integer('registration_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'registration_id' => ['required', 'exists:registrations,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'purpose' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'proposal_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:4096'],
            'supporting_docs' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:4096'],
        ]);

        $registration = Registration::where('user_id', $request->user()->id)
            ->findOrFail($data['registration_id']);

        if (! $registration->canRequestFund()) {
            return redirect()
                ->route('registrations.index')
                ->with('error', 'Pengajuan dana tidak tersedia untuk lomba ini.');
        }

        if ($request->hasFile('proposal_file')) {
            $data['proposal_file'] = $request->file('proposal_file')->store('fund-proposals', 'public');
        }

        if ($request->hasFile('supporting_docs')) {
            $data['supporting_docs'] = $request->file('supporting_docs')->store('fund-supporting-docs', 'public');
        }

        FundRequest::create([
            ...$data,
            'user_id' => $request->user()->id,
            'registration_id' => $registration->id,
            'status' => 'pending',
        ]);

        return redirect()->route('registrations.index')->with('success', 'Pengajuan dana berhasil dikirim.');
    }
}
