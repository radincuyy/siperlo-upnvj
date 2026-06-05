<x-guest-layout>
    <div class="flex items-center gap-3">
        <img src="{{ asset('brand/siperlo-mark.png') }}" alt="" aria-hidden="true" class="h-10 w-10 rounded-md object-contain">
        <span class="font-display text-xl font-bold text-campus-green">SIPERLO</span>
    </div>

    <div class="mt-10">
        <h2 class="font-display text-2xl font-bold leading-tight text-ink">Selamat Datang!</h2>
        <p class="mt-2 text-sm text-muted-ink">Masuk ke akun SIPERLO Anda.</p>
    </div>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Email / NIM / NIP" />
            <x-text-input id="email" class="mt-1 block w-full" type="text" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Masukkan email, NIM, atau NIP" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between text-sm">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-field-border text-campus-green shadow-sm focus:border-campus-green focus:ring-campus-green" name="remember">
                <span class="ms-2 text-muted-ink">Ingat saya</span>
            </label>
            @if (Route::has('password.request'))
                <a class="font-medium text-campus-green hover:underline" href="{{ route('password.request') }}">Lupa password?</a>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3 pt-2">
            <button class="siperlo-btn-primary px-4 py-2.5 text-sm">Masuk</button>
            <a href="{{ route('register') }}" class="siperlo-btn-secondary px-4 py-2.5 text-center text-sm">Daftar akun</a>
        </div>
    </form>

    <div class="my-6 flex items-center gap-3 text-xs uppercase tracking-[0.18em] text-muted-ink">
        <div class="h-px flex-1 bg-border-line"></div>
        <span>atau</span>
        <div class="h-px flex-1 bg-border-line"></div>
    </div>

    <a href="{{ route('google.redirect') }}" class="siperlo-btn-secondary flex items-center justify-center gap-2 px-4 py-2.5 text-sm">
        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
        Masuk dengan Google
    </a>
</x-guest-layout>
