<x-guest-layout>
    <div class="flex items-center gap-3">
        <img src="{{ asset('brand/siperlo-mark.png') }}" alt="" aria-hidden="true" class="h-10 w-10 rounded-md object-contain">
        <span class="font-display text-xl font-bold text-campus-green">SIPERLO</span>
    </div>

    <div class="mt-10">
        <h2 class="font-display text-4xl font-bold leading-tight text-ink">Selamat Datang!</h2>
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

    <a href="{{ route('google.redirect') }}" class="siperlo-btn-secondary block px-4 py-2.5 text-center text-sm">
        Masuk dengan Google
    </a>
</x-guest-layout>
