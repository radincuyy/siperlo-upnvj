@extends('layouts.siperlo')

@section('title', 'Mentor - SIPERLO')
@section('eyebrow', 'Pembimbing Lomba')
@section('page_title', 'Cari Mentor')

@section('content')
<section class="siperlo-surface rounded-md p-5">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="font-display text-xl font-bold">Daftar Mentor</h2>
            <p class="mt-1 text-sm text-ink/80">Mentor bersifat opsional. Pilih dosen hanya jika kamu membutuhkan arahan persiapan lomba.</p>
        </div>
        <span class="siperlo-pill px-3 py-1 text-xs">{{ $mentors->total() }} mentor aktif</span>
    </div>

    <form method="GET" action="{{ route('mentors.index') }}" class="mt-5 grid gap-3 sm:grid-cols-[1fr_120px]">
        <label class="sr-only" for="mentor-search">Cari mentor</label>
        <input id="mentor-search" name="search" value="{{ request('search') }}" placeholder="Cari nama dosen atau bidang keahlian..."
               class="siperlo-field">
        <button class="siperlo-btn-primary px-4 py-2 text-sm">Cari</button>
    </form>
</section>

<div class="mt-5 grid gap-4 lg:grid-cols-3">
    @forelse ($mentors as $mentor)
        <article class="siperlo-surface rounded-md p-5">
            <div class="flex items-start gap-4">
                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-md border border-border-line bg-admin-note-surface">
                    <img src="{{ asset('brand/mentor-placeholder.svg') }}"
                         alt=""
                         aria-hidden="true"
                         loading="lazy"
                         decoding="async"
                         class="h-full w-full object-cover">
                </div>
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-campus-green">{{ $mentor->expertise }}</div>
                    <h3 class="mt-2 font-display text-xl font-bold leading-snug">{{ $mentor->user->name }}</h3>
                    <span class="siperlo-status siperlo-status-success mt-2">Aktif</span>
                </div>
            </div>
            <p class="mt-4 line-clamp-3 text-sm leading-6 text-ink/80">{{ $mentor->bio }}</p>
            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-md border border-border-line bg-admin-note-surface p-3">
                    <div class="font-display text-2xl font-bold text-campus-green">{{ $mentor->total_mentored }}</div>
                    <div class="text-muted-ink">Bimbingan</div>
                </div>
                <div class="rounded-md border border-border-line bg-admin-note-surface p-3">
                    <div class="font-display text-2xl font-bold text-campus-green">{{ $mentor->achievements->count() }}</div>
                    <div class="text-muted-ink">Achievement</div>
                </div>
            </div>
            <a href="{{ route('mentors.show', $mentor) }}" class="siperlo-btn-secondary mt-4 block px-4 py-2 text-center text-sm">Lihat Profil</a>
        </article>
    @empty
        <div class="siperlo-surface rounded-md p-8 text-center text-muted-ink lg:col-span-3">
            <img src="{{ asset('brand/siperlo-empty.svg') }}" alt="" aria-hidden="true" loading="lazy" decoding="async" class="mx-auto mb-4 h-28 w-auto">
            <h3 class="font-display text-xl font-bold text-ink">Belum ada mentor yang cocok.</h3>
            <p class="mt-2 text-sm">Coba gunakan nama dosen atau bidang keahlian yang lebih umum.</p>
        </div>
    @endforelse
</div>

<div class="mt-5">{{ $mentors->links() }}</div>
@endsection
