<x-guest-layout>
    <div class="siperlo-surface rounded-md p-8">
        <div class="text-center">
            <img src="{{ asset('brand/siperlo-mark.png') }}" alt="Logo SIPERLO" class="mx-auto mb-4 h-14 w-14 rounded-md object-contain lg:hidden">
            <h2 class="font-display text-2xl font-bold">Masuk ke SIPERLO</h2>
            <p class="mt-2 text-sm text-muted-ink">Akses layanan perlombaan mahasiswa.</p>
        </div>

        <x-auth-session-status class="mt-5" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
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

            <button class="siperlo-btn-primary w-full px-4 py-2.5 text-sm">Masuk</button>
        </form>

        <div class="my-6 flex items-center gap-3 text-xs text-muted-ink">
            <div class="h-px flex-1 bg-border-line"></div>
            <span>atau</span>
            <div class="h-px flex-1 bg-border-line"></div>
        </div>

        <a href="{{ route('google.redirect') }}" class="siperlo-btn-secondary block px-4 py-2.5 text-center text-sm">
            Masuk dengan Google
        </a>

        <p class="mt-6 text-center text-sm text-muted-ink">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-campus-green hover:underline">Daftar akun</a>
        </p>
    </div>
</x-guest-layout>
