<x-guest-layout>
    <div class="siperlo-surface rounded-md p-8">
        <div class="text-center">
            <h2 class="font-display text-2xl font-bold">Reset Password</h2>
            <p class="mt-2 text-sm text-muted-ink">Buat password baru untuk akun SIPERLO kamu.</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" value="Password Baru" />
                <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Konfirmasi Password" />
                <x-text-input id="password_confirmation" class="mt-1" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <button class="siperlo-btn-primary w-full px-4 py-2.5 text-sm">Simpan Password Baru</button>
        </form>
    </div>
</x-guest-layout>
