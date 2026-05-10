<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function index(): View
    {
        return view('admin.competitions.index', [
            'competitions' => Competition::withCount('registrations')->latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.competitions.create', [
            'competition' => new Competition(['status' => 'open', 'fee' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('poster_image')) {
            $data['poster_image'] = $request->file('poster_image')->store('competition-posters', 'public');
        }

        if ($request->hasFile('guidebook_file')) {
            $data['guidebook_file'] = $request->file('guidebook_file')->store('competition-guidebooks', 'public');
        }

        Competition::create($data);

        return redirect()->route('admin.competitions.index')->with('success', 'Data lomba berhasil ditambahkan.');
    }

    public function show(Competition $competition): View
    {
        return view('admin.competitions.show', [
            'competition' => $competition->load(['registrations.user', 'registrations.mentor.user']),
        ]);
    }

    public function edit(Competition $competition): View
    {
        return view('admin.competitions.edit', [
            'competition' => $competition,
        ]);
    }

    public function update(Request $request, Competition $competition): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('poster_image')) {
            if ($competition->poster_image) {
                Storage::disk('public')->delete($competition->poster_image);
            }

            $data['poster_image'] = $request->file('poster_image')->store('competition-posters', 'public');
        }

        if ($request->hasFile('guidebook_file')) {
            if ($competition->guidebook_file) {
                Storage::disk('public')->delete($competition->guidebook_file);
            }

            $data['guidebook_file'] = $request->file('guidebook_file')->store('competition-guidebooks', 'public');
        }

        $competition->update($data);

        return redirect()->route('admin.competitions.index')->with('success', 'Data lomba berhasil diperbarui.');
    }

    public function destroy(Competition $competition): RedirectResponse
    {
        if ($competition->poster_image) {
            Storage::disk('public')->delete($competition->poster_image);
        }

        if ($competition->guidebook_file) {
            Storage::disk('public')->delete($competition->guidebook_file);
        }

        $competition->delete();

        return redirect()->route('admin.competitions.index')->with('success', 'Data lomba berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'organizer' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(array_keys(Competition::CATEGORIES))],
            'type' => ['nullable', 'string', Rule::in(array_keys(Competition::TYPES))],
            'registration_deadline' => ['required', 'date'],
            'event_start' => ['nullable', 'date'],
            'event_end' => ['nullable', 'date', 'after_or_equal:event_start'],
            'location' => ['nullable', 'string', 'max:255'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'poster_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'guidebook_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'contact_person_name' => ['nullable', 'string', 'max:255'],
            'contact_person_phone' => ['nullable', 'string', 'max:50'],
            'contact_person_email' => ['nullable', 'email', 'max:255'],
            'official_website' => ['nullable', 'url', 'max:255'],
            'social_media' => ['nullable', 'url', 'max:255'],
            'external_registration_url' => ['nullable', 'url', 'max:255'],
            'requirements' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'timeline' => ['nullable', 'string'],
            'status' => ['required', 'in:open,soon,closed,draft'],
        ]);
    }
}
