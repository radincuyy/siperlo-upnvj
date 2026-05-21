@extends('layouts.siperlo')

@section('title', 'Detail Lomba Admin - SIPERLO')
@section('page_title', $competition->title)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Kelola Lomba', 'route' => 'admin.competitions.index'],
        ['label' => $competition->title],
    ]" />
@endsection

@section('content')
@php
    $posterUrl = $competition->poster_image
        ? \Illuminate\Support\Facades\Storage::url($competition->poster_image)
        : asset('brand/siperlo-campus.svg');

    $guidebookUrl = $competition->guidebook_file
        ? \Illuminate\Support\Facades\Storage::url($competition->guidebook_file)
        : null;

    $statusLabels = [
        'open' => 'Pendaftaran Buka',
        'soon' => 'Akan Datang',
        'closed' => 'Ditutup',
        'draft' => 'Draft',
    ];

    $statusClasses = [
        'open' => 'siperlo-status siperlo-status-success',
        'soon' => 'siperlo-status siperlo-status-neutral',
        'closed' => 'siperlo-status siperlo-status-warning',
        'draft' => 'siperlo-status siperlo-status-neutral',
    ];
@endphp

<div class="grid gap-6 xl:grid-cols-[1fr_340px]">
    <section class="space-y-5">
        <div class="siperlo-surface overflow-hidden rounded-md">
            <div class="grid gap-0 lg:grid-cols-[280px_1fr]">
                <div class="bg-soft-green p-4">
                    <img src="{{ $posterUrl }}" alt="Poster atau ilustrasi lomba {{ $competition->title }}" decoding="async" class="aspect-[4/5] w-full rounded-md border border-border-line object-cover">
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-muted-ink">{{ $competition->category }} - {{ $competition->type ?: 'Umum' }}</div>
                            <h2 class="mt-1 font-display text-2xl font-bold">{{ $competition->title }}</h2>
                        </div>
                        <span class="{{ $statusClasses[$competition->displayStatus()] ?? 'siperlo-status siperlo-status-neutral' }}">
                            {{ $statusLabels[$competition->displayStatus()] ?? ucfirst($competition->displayStatus()) }}
                        </span>
                    </div>

                    <p class="mt-5 leading-7 text-ink/80">{{ $competition->description ?: 'Belum ada deskripsi lomba.' }}</p>

                    <dl class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <div class="siperlo-data-cell">
                            <dt class="font-semibold text-ink">Deadline</dt>
                            <dd class="mt-1 text-ink/80">{{ $competition->registration_deadline->translatedFormat('d M Y H:i') }}</dd>
                        </div>
                        <div class="siperlo-data-cell">
                            <dt class="font-semibold text-ink">Pelaksanaan</dt>
                            <dd class="mt-1 text-ink/80">{{ optional($competition->event_start)->translatedFormat('d M Y') ?: '-' }} sampai {{ optional($competition->event_end)->translatedFormat('d M Y') ?: '-' }}</dd>
                        </div>
                        <div class="siperlo-data-cell">
                            <dt class="font-semibold text-ink">Biaya</dt>
                            <dd class="mt-1 text-ink/80">{{ $competition->fee > 0 ? 'Rp '.number_format($competition->fee, 0, ',', '.') : 'Gratis' }}</dd>
                        </div>
                        <div class="siperlo-data-cell">
                            <dt class="font-semibold text-ink">Pendaftar</dt>
                            <dd class="mt-1 text-ink/80">{{ $competition->registrations->count() }} mahasiswa</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="siperlo-surface rounded-md p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-display text-lg font-bold">Pendaftar</h2>
                <span class="siperlo-status siperlo-status-neutral">{{ $competition->registrations->count() }} data</span>
            </div>

            <div class="mt-4 divide-y divide-border-line">
                @forelse ($competition->registrations as $registration)
                    <div class="grid gap-3 py-4 text-sm md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-ink">{{ $registration->user->name }}</div>
                            <div class="mt-1 text-ink/80">Mentor: {{ $registration->mentor?->user?->name ?: 'Belum ditentukan' }}</div>
                            <div class="text-ink/80">Laporan hasil: {{ $registration->resultStatusLabel() }}</div>
                        </div>
                        <span class="siperlo-status {{ $registration->primaryStatus() === 'finished' ? 'siperlo-status-success' : ($registration->primaryStatus() === 'ongoing' ? 'siperlo-status-info' : 'siperlo-status-neutral') }}">
                            {{ $registration->primaryStatusLabel() }}
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-sm text-muted-ink">Belum ada pendaftar.</div>
                @endforelse
            </div>
        </div>
    </section>

    <aside class="space-y-5">
        <div class="siperlo-surface rounded-md p-5">
            <h2 class="font-display text-lg font-bold">Aksi Admin</h2>
            <div class="mt-4 grid gap-3">
                <a href="{{ route('admin.competitions.edit', $competition) }}" class="siperlo-btn-primary px-4 py-2 text-sm">Edit Lomba</a>
                <a href="{{ route('admin.competitions.index') }}" class="siperlo-btn-secondary px-4 py-2 text-sm">Kembali ke Daftar</a>
            </div>
        </div>

        <div class="siperlo-surface rounded-md p-5">
            <h2 class="font-display text-lg font-bold">Contact Person</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="siperlo-data-cell">
                    <dt class="font-semibold text-ink">Nama</dt>
                    <dd class="mt-1 text-ink/80">{{ $competition->contact_person_name ?: '-' }}</dd>
                </div>
                <div class="siperlo-data-cell">
                    <dt class="font-semibold text-ink">Telepon</dt>
                    <dd class="mt-1 text-ink/80">{{ $competition->contact_person_phone ?: '-' }}</dd>
                </div>
                <div class="siperlo-data-cell">
                    <dt class="font-semibold text-ink">Email</dt>
                    <dd class="mt-1 text-ink/80">{{ $competition->contact_person_email ?: '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="siperlo-surface rounded-md p-5">
            <h2 class="font-display text-lg font-bold">Dokumen dan Link</h2>
            <div class="mt-4 grid gap-3">
                @if ($guidebookUrl)
                    <a href="{{ $guidebookUrl }}" target="_blank" rel="noopener" class="siperlo-btn-secondary px-4 py-2 text-sm">Lihat Guidebook</a>
                @else
                    <div class="rounded-md border border-border-line bg-admin-note-surface p-3 text-sm text-ink/80">Guidebook belum diunggah.</div>
                @endif

                @if ($competition->official_website)
                    <a href="{{ $competition->official_website }}" target="_blank" rel="noopener" class="siperlo-btn-secondary px-4 py-2 text-sm">Website Resmi</a>
                @endif
                @if ($competition->social_media)
                    <a href="{{ $competition->social_media }}" target="_blank" rel="noopener" class="siperlo-btn-secondary px-4 py-2 text-sm">Sosial Media</a>
                @endif
                @if ($competition->external_registration_url)
                    <a href="{{ $competition->external_registration_url }}" target="_blank" rel="noopener" class="siperlo-btn-secondary px-4 py-2 text-sm">Link Pendaftaran Eksternal</a>
                @endif
            </div>
        </div>
    </aside>
</div>
@endsection
