<x-guest-layout>
    <div class="siperlo-surface rounded-md p-8">
        <div class="text-center">
            <h2 class="font-display text-2xl font-bold">Konfirmasi Password</h2>
            <p class="mt-2 text-sm text-muted-ink">Area aman. Masukkan password untuk melanjutkan.</p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="current-password" autofocus />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <button class="siperlo-btn-primary w-full px-4 py-2.5 text-sm">Konfirmasi</button>
        </form>
    </div>
</x-guest-layout>
