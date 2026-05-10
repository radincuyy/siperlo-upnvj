<x-guest-layout>
    <div class="siperlo-surface rounded-md p-8">
        <div class="text-center">
            <h2 class="font-display text-2xl font-bold">Lupa Password</h2>
            <p class="mt-2 text-sm text-muted-ink">Masukkan email akun SIPERLO untuk menerima tautan reset password.</p>
        </div>

        <x-auth-session-status class="mt-5" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <button class="siperlo-btn-primary w-full px-4 py-2.5 text-sm">Kirim Tautan Reset</button>
        </form>

        <p class="mt-6 text-center text-sm text-muted-ink">
            Ingat password?
            <a href="{{ route('login') }}" class="font-semibold text-campus-green hover:underline">Masuk</a>
        </p>
    </div>
</x-guest-layout>
