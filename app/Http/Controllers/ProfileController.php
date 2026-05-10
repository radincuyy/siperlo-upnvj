<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

final class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        /** @var User $user */
        $user = $request->user();

        // Guard: admin terakhir tidak boleh menghapus dirinya sendiri, jika tidak
        // app akan kehilangan satu-satunya akses kontrol.
        if ($user->isRole('admin')) {
            $adminCount = User::query()->where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return Redirect::route('profile.edit')->withErrors([
                    'userDeletion' => 'Akun admin terakhir tidak dapat dihapus. Buat akun admin lain terlebih dulu.',
                ], 'userDeletion');
            }
        }

        // Cek apakah user masih punya aktivitas yang sedang berjalan.
        if ($this->hasActiveActivity($user)) {
            return Redirect::route('profile.edit')->withErrors([
                'userDeletion' => 'Akun tidak dapat dihapus karena masih memiliki pendaftaran lomba, pengajuan mentor, atau pengajuan dana yang aktif. Selesaikan prosesnya terlebih dulu atau hubungi admin.',
            ], 'userDeletion');
        }

        DB::transaction(function () use ($user, $request): void {
            Auth::logout();

            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();
        });

        return Redirect::to('/');
    }

    private function hasActiveActivity(User $user): bool
    {
        // Registrasi yang belum selesai (masih registered/ongoing, bukan finished).
        $hasActiveRegistration = $user->registrations()
            ->whereIn('status', ['registered', 'ongoing'])
            ->exists();

        if ($hasActiveRegistration) {
            return true;
        }

        $hasPendingMentorRequest = $user->mentorRequests()
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingMentorRequest) {
            return true;
        }

        return $user->fundRequests()
            ->where('status', 'pending')
            ->exists();
    }
}
