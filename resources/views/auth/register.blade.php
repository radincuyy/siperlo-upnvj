<x-guest-layout>
    <div class="flex items-center gap-3">
        <img src="{{ asset('brand/siperlo-mark.png') }}" alt="" aria-hidden="true" class="h-10 w-10 rounded-md object-contain">
        <span class="font-display text-xl font-bold text-campus-green">SIPERLO</span>
    </div>

    <div class="mt-10">
        <h2 class="font-display text-4xl font-bold leading-tight text-ink">Daftar Akun</h2>
        <p class="mt-2 text-sm text-muted-ink">Akun baru otomatis dibuat sebagai mahasiswa.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="nim_nip" value="NIM / NIP" />
                <x-text-input id="nim_nip" class="mt-1 block w-full" type="text" name="nim_nip" :value="old('nim_nip')" />
                <x-input-error :messages="$errors->get('nim_nip')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="phone" value="Telepon" />
                <x-text-input id="phone" class="mt-1 block w-full" type="text" name="phone" :value="old('phone')" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="faculty" value="Fakultas" />
                <x-text-input id="faculty" class="mt-1 block w-full" type="text" name="faculty" :value="old('faculty')" />
                <x-input-error :messages="$errors->get('faculty')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="major" value="Program Studi" />
                <x-text-input id="major" class="mt-1 block w-full" type="text" name="major" :value="old('major')" />
                <x-input-error :messages="$errors->get('major')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi Password" />
            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button class="siperlo-btn-primary w-full px-4 py-2.5 text-sm">Daftar</button>
    </form>

    <p class="mt-6 text-center text-sm text-muted-ink">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-campus-green hover:underline">Masuk</a>
    </p>
</x-guest-layout>
