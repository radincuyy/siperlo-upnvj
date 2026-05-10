@extends('layouts.siperlo')

@section('title', 'Profil - SIPERLO')
@section('eyebrow', 'Mahasiswa')
@section('page_title', 'Profil')

@section('content')
<div class="grid gap-6 xl:grid-cols-[1fr_360px]">
    <section class="space-y-5">
        <div class="siperlo-surface rounded-md p-6">
            <h2 class="font-display text-xl font-bold">Informasi Akun</h2>
            <p class="mt-1 text-sm text-muted-ink">Perbarui nama dan email yang digunakan untuk aktivitas SIPERLO.</p>

            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                @csrf
            </form>

            <form method="post" action="{{ route('profile.update') }}" class="mt-5 grid gap-4">
                @csrf
                @method('patch')

                <div>
                    <label for="name" class="text-sm font-semibold">Nama <span class="siperlo-required" aria-hidden="true">*</span></label>
                    <input id="name" name="name" type="text" class="siperlo-field mt-1 w-full" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                           aria-invalid="@error('name') true @else false @enderror"
                           @error('name') aria-describedby="name-error" @enderror>
                    @error('name')
                        <div id="name-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="email" class="text-sm font-semibold">Email <span class="siperlo-required" aria-hidden="true">*</span></label>
                    <input id="email" name="email" type="email" class="siperlo-field mt-1 w-full" value="{{ old('email', $user->email) }}" required autocomplete="username"
                           aria-invalid="@error('email') true @else false @enderror"
                           @error('email') aria-describedby="email-error" @enderror>
                    @error('email')
                        <div id="email-error" class="siperlo-error">{{ $message }}</div>
                    @enderror

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                            Email belum diverifikasi.
                            <button form="send-verification" class="font-semibold underline">Kirim ulang verifikasi</button>

                            @if (session('status') === 'verification-link-sent')
                                <div class="mt-1 font-semibold">Link verifikasi baru sudah dikirim.</div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button class="siperlo-btn-primary px-5 py-2 text-sm">Simpan Profil</button>
                    @if (session('status') === 'profile-updated')
                        <span class="text-sm font-semibold text-campus-green">Profil tersimpan.</span>
                    @endif
                </div>
            </form>
        </div>

        <div class="siperlo-surface rounded-md p-6">
            <h2 class="font-display text-xl font-bold">Keamanan Password</h2>
            <p class="mt-1 text-sm text-muted-ink">Gunakan password yang kuat untuk menjaga akses akun.</p>

            <form method="post" action="{{ route('password.update') }}" class="mt-5 grid gap-4">
                @csrf
                @method('put')

                <div>
                    <label for="update_password_current_password" class="text-sm font-semibold">Password Saat Ini <span class="siperlo-required" aria-hidden="true">*</span></label>
                    <input id="update_password_current_password" name="current_password" type="password" class="siperlo-field mt-1 w-full" autocomplete="current-password" required
                           aria-invalid="{{ $errors->updatePassword->has('current_password') ? 'true' : 'false' }}"
                           @if ($errors->updatePassword->has('current_password')) aria-describedby="update-password-current-error" @endif>
                    @if ($errors->updatePassword->has('current_password'))
                        <div id="update-password-current-error" class="siperlo-error">{{ implode(' ', $errors->updatePassword->get('current_password')) }}</div>
                    @endif
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="update_password_password" class="text-sm font-semibold">Password Baru <span class="siperlo-required" aria-hidden="true">*</span></label>
                        <input id="update_password_password" name="password" type="password" class="siperlo-field mt-1 w-full" autocomplete="new-password" required
                               aria-invalid="{{ $errors->updatePassword->has('password') ? 'true' : 'false' }}"
                               @if ($errors->updatePassword->has('password')) aria-describedby="update-password-error" @endif>
                        @if ($errors->updatePassword->has('password'))
                            <div id="update-password-error" class="siperlo-error">{{ implode(' ', $errors->updatePassword->get('password')) }}</div>
                        @endif
                    </div>
                    <div>
                        <label for="update_password_password_confirmation" class="text-sm font-semibold">Konfirmasi Password <span class="siperlo-required" aria-hidden="true">*</span></label>
                        <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="siperlo-field mt-1 w-full" autocomplete="new-password" required
                               aria-invalid="{{ $errors->updatePassword->has('password_confirmation') ? 'true' : 'false' }}"
                               @if ($errors->updatePassword->has('password_confirmation')) aria-describedby="update-password-confirmation-error" @endif>
                        @if ($errors->updatePassword->has('password_confirmation'))
                            <div id="update-password-confirmation-error" class="siperlo-error">{{ implode(' ', $errors->updatePassword->get('password_confirmation')) }}</div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button class="siperlo-btn-primary px-5 py-2 text-sm">Simpan Password</button>
                    @if (session('status') === 'password-updated')
                        <span class="text-sm font-semibold text-campus-green">Password tersimpan.</span>
                    @endif
                </div>
            </form>
        </div>
    </section>

    <aside class="space-y-5">
        <div class="siperlo-surface rounded-md p-5">
            <h2 class="font-display text-lg font-bold">Ringkasan Akun</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="rounded-md border border-border-line bg-admin-note-surface p-3">
                    <dt class="font-semibold text-ink">Nama</dt>
                    <dd class="mt-1 text-ink/80">{{ $user->name }}</dd>
                </div>
                <div class="rounded-md border border-border-line bg-admin-note-surface p-3">
                    <dt class="font-semibold text-ink">Email</dt>
                    <dd class="mt-1 text-ink/80">{{ $user->email }}</dd>
                </div>
                <div class="rounded-md border border-border-line bg-admin-note-surface p-3">
                    <dt class="font-semibold text-ink">Role</dt>
                    <dd class="mt-1 text-ink/80">{{ ucfirst($user->role) }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/40 p-5">
            <h2 class="font-display text-lg font-bold text-red-900">Hapus Akun</h2>
            <p class="mt-2 text-sm leading-6 text-ink/80">Aksi ini menghapus akun secara permanen. Gunakan hanya jika benar-benar diperlukan.</p>

            <form method="post" action="{{ route('profile.destroy') }}" class="mt-4 space-y-3">
                @csrf
                @method('delete')

                <div>
                    <label for="delete_password" class="text-sm font-semibold">Password <span class="siperlo-required" aria-hidden="true">*</span></label>
                    <input id="delete_password" name="password" type="password" class="siperlo-field mt-1 w-full" placeholder="Masukkan password untuk konfirmasi" required
                           aria-invalid="{{ $errors->userDeletion->has('password') ? 'true' : 'false' }}"
                           @if ($errors->userDeletion->has('password')) aria-describedby="delete-password-error" @endif>
                    @if ($errors->userDeletion->has('password'))
                        <div id="delete-password-error" class="siperlo-error">{{ implode(' ', $errors->userDeletion->get('password')) }}</div>
                    @endif
                </div>

                <button class="siperlo-btn-danger w-full px-4 py-2 text-sm">Hapus Akun</button>
            </form>
        </div>
    </aside>
</div>
@endsection
