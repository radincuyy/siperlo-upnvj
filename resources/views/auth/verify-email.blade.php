<x-guest-layout>
    <div class="siperlo-surface rounded-md p-8">
        <div class="text-center">
            <h2 class="font-display text-2xl font-bold">Verifikasi Email</h2>
            <p class="mt-3 text-sm text-ink/80">
                Terima kasih sudah mendaftar. Buka email kamu dan klik tautan verifikasi yang baru saja dikirim.
                Jika tidak menemukannya, minta kirim ulang di bawah.
            </p>
        </div>

        @if (session('status') === 'verification-link-sent')
            <div role="status" aria-live="polite" class="mt-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                Tautan verifikasi baru sudah dikirim ke email kamu.
            </div>
        @endif

        <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="siperlo-btn-primary px-4 py-2 text-sm">Kirim Ulang Email Verifikasi</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm font-semibold text-muted-ink underline hover:text-campus-green">Keluar</button>
            </form>
        </div>
    </div>
</x-guest-layout>
