<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultReportController extends Controller
{
    public function show(Request $request, Registration $registration): View
    {
        $registration = $this->ownedRegistration($request, $registration);

        return view('registrations.report_result', [
            'registration' => $registration->load('competition'),
            'readOnly' => true,
        ]);
    }

    public function create(Request $request, Registration $registration): View|RedirectResponse
    {
        $registration = $this->ownedRegistration($request, $registration);

        if (! $registration->canReportResult()) {
            return redirect()
                ->route('registrations.index')
                ->with('error', 'Laporan hasil tersedia setelah status lomba ditandai Berlangsung oleh admin.');
        }

        return view('registrations.report_result', [
            'registration' => $registration->load('competition'),
            'readOnly' => false,
        ]);
    }

    public function store(Request $request, Registration $registration): RedirectResponse
    {
        $registration = $this->ownedRegistration($request, $registration);

        if (! $registration->canReportResult()) {
            return redirect()
                ->route('registrations.index')
                ->with('error', 'Laporan hasil tersedia setelah status lomba ditandai Berlangsung oleh admin.');
        }

        $data = $request->validate([
            'result' => ['required', 'string', 'max:255'],
            'result_description' => ['nullable', 'string'],
            'result_proof_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:4096'],
        ]);

        if ($request->hasFile('result_proof_file')) {
            $data['result_proof_file'] = $request->file('result_proof_file')->store('result-proofs', 'public');
        }

        $registration->update([
            ...$data,
            'result_status' => 'pending',
            'result_submitted_at' => now(),
            'result_reviewed_at' => null,
            'result_admin_notes' => null,
        ]);

        return redirect()
            ->route('registrations.index')
            ->with('success', 'Laporan hasil berhasil dikirim dan menunggu validasi admin.');
    }

    private function ownedRegistration(Request $request, Registration $registration): Registration
    {
        abort_unless((int) $registration->user_id === (int) $request->user()->id, 404);

        return $registration;
    }
}
