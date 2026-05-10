<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

final class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()
                ->route('login')
                ->with('status', 'Google Login belum dikonfigurasi. Gunakan email dan password untuk demo lokal.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            /** @var SocialiteUser $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect()
                ->route('login')
                ->with('status', 'Login Google gagal. Silakan coba lagi atau gunakan login email.');
        }

        $email = $googleUser->getEmail();
        $googleId = $googleUser->getId();

        if ($email === null || $googleId === null) {
            return redirect()
                ->route('login')
                ->with('status', 'Login Google gagal: profil tidak lengkap.');
        }

        $existingByGoogleId = User::query()->where('google_id', $googleId)->first();
        if ($existingByGoogleId !== null) {
            Auth::login($existingByGoogleId, remember: true);

            return redirect()->route($existingByGoogleId->dashboardRoute());
        }

        $existingByEmail = User::query()->where('email', $email)->first();
        if ($existingByEmail !== null) {
            if ($existingByEmail->google_id !== null) {
                return redirect()
                    ->route('login')
                    ->with('status', 'Akun ini sudah terhubung ke Google lain. Gunakan akun Google yang benar atau hubungi admin.');
            }

            return redirect()
                ->route('login')
                ->with('status', 'Email ini sudah terdaftar. Masuk dengan password terlebih dulu, lalu hubungkan Google dari halaman profil.');
        }

        $user = User::create([
            'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Pengguna SIPERLO',
            'email' => $email,
            'password' => Hash::make(Str::random(40)),
        ]);

        $user->role = 'mahasiswa';
        $user->google_id = $googleId;
        $user->email_verified_at = now();
        $user->save();

        Auth::login($user, remember: true);

        return redirect()->route($user->dashboardRoute());
    }
}
