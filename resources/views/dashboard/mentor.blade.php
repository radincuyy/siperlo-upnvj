@extends('layouts.siperlo')

@section('title', 'Dashboard Mentor - SIPERLO')
@section('eyebrow', 'Area Mentor')
@section('page_title', 'Dashboard Mentor')

@section('content')
@if (! $mentor)
    <div class="rounded-md border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
        Akun ini memiliki role mentor, tetapi profil mentor belum dibuat oleh admin.
    </div>
@else
    @php
        $supportStatusLabels = [
            'pending' => 'Menunggu review',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'revision' => 'Perlu revisi',
        ];
        $statusStyles = [
            'pending' => 'siperlo-status siperlo-status-warning',
            'approved' => 'siperlo-status siperlo-status-success',
            'revision' => 'siperlo-status siperlo-status-warning',
            'rejected' => 'siperlo-status siperlo-status-danger',
        ];
        $pendingCount = $requests->where('status', 'pending')->count();
    @endphp

    <div class="siperlo-surface rounded-md p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="text-xs font-semibold uppercase text-muted-ink">Bidang</div>
                <h2 class="mt-1 font-display text-xl font-bold">{{ $mentor->expertise }}</h2>
                <p class="mt-2 text-sm text-ink/80 line-clamp-2">{{ $mentor->bio }}</p>
            </div>
            <span class="siperlo-status siperlo-status-success">Aktif</span>
        </div>
        <dl class="mt-5 grid gap-3 sm:grid-cols-3">
            <div class="siperlo-data-cell">
                <dt class="text-xs font-semibold uppercase text-muted-ink">Total Bimbingan</dt>
                <dd class="mt-1 font-display text-2xl font-bold text-campus-green">{{ $mentor->total_mentored }}</dd>
            </div>
            <div class="siperlo-data-cell">
                <dt class="text-xs font-semibold uppercase text-muted-ink">Pengajuan Masuk</dt>
                <dd class="mt-1 font-display text-2xl font-bold text-campus-green">{{ $requests->count() }}</dd>
            </div>
            <div class="siperlo-data-cell">
                <dt class="text-xs font-semibold uppercase text-muted-ink">Perlu Tindakan</dt>
                <dd class="mt-1 font-display text-2xl font-bold {{ $pendingCount > 0 ? 'text-amber-800' : 'text-campus-green' }}">{{ $pendingCount }}</dd>
            </div>
        </dl>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-2">
        <section class="siperlo-surface rounded-md p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-display text-lg font-bold">Pengajuan Mentor</h2>
                <span class="siperlo-status siperlo-status-neutral">{{ $requests->count() }} data</span>
            </div>
            <div class="mt-4 divide-y divide-border-line">
                @forelse ($requests as $request)
                    @php $badgeClass = $statusStyles[$request->status] ?? 'siperlo-status siperlo-status-neutral'; @endphp
                    <div class="py-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-semibold text-ink">{{ $request->user->name }}</div>
                                <div class="mt-1 text-sm text-ink/80">{{ $request->registration->competition->title }}</div>
                            </div>
                            <span class="{{ $badgeClass }}">{{ $supportStatusLabels[$request->status] ?? $request->status }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-3 text-sm text-muted-ink">Belum ada pengajuan mentor.</div>
                @endforelse
            </div>
        </section>

        <section class="siperlo-surface rounded-md p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-display text-lg font-bold">Mahasiswa Dibimbing</h2>
                <span class="siperlo-status siperlo-status-neutral">{{ $registrations->count() }} data</span>
            </div>
            <div class="mt-4 divide-y divide-border-line">
                @forelse ($registrations as $registration)
                    <div class="py-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-semibold text-ink">{{ $registration->user->name }}</div>
                                <div class="mt-1 text-sm text-ink/80">{{ $registration->competition->title }}</div>
                            </div>
                            <span class="siperlo-status {{ $registration->primaryStatus() === 'finished' ? 'siperlo-status-success' : ($registration->primaryStatus() === 'ongoing' ? 'siperlo-status-info' : 'siperlo-status-neutral') }}">
                                {{ $registration->primaryStatusLabel() }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-3 text-sm text-muted-ink">Belum ada mahasiswa dibimbing.</div>
                @endforelse
            </div>
        </section>
    </div>
@endif
@endsection
