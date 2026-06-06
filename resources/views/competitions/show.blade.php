@extends('layouts.siperlo')

@section('title', $competition->title.' - SIPERLO')
@section('page_title', $competition->title)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Daftar Lomba', 'route' => 'competitions.index'],
        ['label' => $competition->title],
    ]" />
@endsection

@section('content')
@php
    $posterUrl = $competition->poster_image
        ? (str_starts_with($competition->poster_image, 'http') ? $competition->poster_image : \Illuminate\Support\Facades\Storage::url($competition->poster_image))
        : asset('brand/siperlo-campus.svg');
    $guidebookUrl = $competition->guidebook_file
        ? \Illuminate\Support\Facades\Storage::url($competition->guidebook_file)
        : null;

    $supportStatusLabels = [
        'pending' => 'Menunggu review',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'revision' => 'Perlu revisi',
    ];
    $latestMentorRequest = $registration?->mentorRequests?->sortByDesc('created_at')->first();
    $mentorStatus = $registration?->mentor
        ? $registration->mentor->user->name
        : ($latestMentorRequest ? ($supportStatusLabels[$latestMentorRequest->status] ?? $latestMentorRequest->status) : 'Belum diajukan');
    $competitionDisplayStatus = $competition->displayStatus();
    $competitionStatusClass = match ($competitionDisplayStatus) {
        'open' => 'siperlo-status siperlo-status-success',
        'soon' => 'siperlo-status siperlo-status-neutral',
        'closed' => 'siperlo-status siperlo-status-warning',
        default => 'siperlo-status siperlo-status-neutral',
    };
    $statusLabel = match ($competitionDisplayStatus) {
        'open' => 'Pendaftaran Buka',
        'soon' => 'Akan Datang',
        default => 'Ditutup',
    };
    $feeLabel = $competition->fee > 0 ? 'Rp '.number_format($competition->fee, 0, ',', '.') : 'Gratis';
    $eventStart = optional($competition->event_start)->translatedFormat('d M Y');
    $eventEnd = optional($competition->event_end)->translatedFormat('d M Y');
    $eventLabel = match (true) {
        filled($eventStart) && filled($eventEnd) && $eventStart !== $eventEnd => "$eventStart sampai $eventEnd",
        filled($eventStart) => $eventStart,
        default => 'Menunggu jadwal resmi',
    };
    $isFinishedRegistration = $registration && $registration->primaryStatus() === 'finished';
    $isClosed = $competitionDisplayStatus === 'closed';
    $documentsRedam = $isFinishedRegistration || $isClosed;
@endphp

<div class="mx-auto max-w-4xl space-y-5">

    {{-- 1. POSTER (kiri) + JUDUL & INFO (kanan) --}}
    <div class="siperlo-surface overflow-hidden rounded-md">
        <div class="grid grid-cols-[240px_1fr]">
            {{-- Poster --}}
            <div class="flex items-center justify-center bg-soft-green p-4">
                <img src="{{ $posterUrl }}"
                     alt="Poster lomba {{ $competition->title }}"
                     loading="lazy"
                     decoding="async"
                     class="w-full rounded-md border border-border-line object-contain"
                     style="max-height: 340px;">
            </div>

            {{-- Judul + Quick Info --}}
            <div class="flex flex-col p-6">
                {{-- Judul --}}
                <div>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold uppercase tracking-[0.14em] text-muted-ink">{{ $competition->category }} · {{ $competition->type ?: 'Umum' }}</div>
                            <h2 class="mt-1.5 font-display text-2xl font-bold leading-tight">{{ $competition->title }}</h2>
                            <div class="mt-1 text-sm text-muted-ink">{{ $competition->organizer }}</div>
                        </div>
                        <span class="{{ $competitionStatusClass }} shrink-0">{{ $statusLabel }}</span>
                    </div>
                </div>

                {{-- Quick Info Grid --}}
                <dl class="mt-5 grid grid-cols-2 gap-x-6 gap-y-3 border-t border-border-line pt-5 text-sm">
                    <div class="flex items-start gap-2">
                        <x-lucide-calendar class="mt-0.5 h-4 w-4 shrink-0 text-campus-green" aria-hidden="true" />
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-muted-ink">Deadline</dt>
                            <dd class="font-semibold text-ink">{{ $competition->registration_deadline->translatedFormat('d M Y') }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <x-lucide-map-pin class="mt-0.5 h-4 w-4 shrink-0 text-campus-green" aria-hidden="true" />
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-muted-ink">Lokasi</dt>
                            <dd class="font-semibold text-ink">{{ $competition->location ?: 'Belum ditentukan' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <x-lucide-wallet class="mt-0.5 h-4 w-4 shrink-0 text-campus-green" aria-hidden="true" />
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-muted-ink">Biaya</dt>
                            <dd class="font-semibold text-ink">{{ $feeLabel }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <x-lucide-users class="mt-0.5 h-4 w-4 shrink-0 text-campus-green" aria-hidden="true" />
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-muted-ink">Pendaftar</dt>
                            <dd class="font-semibold text-ink">{{ $competition->registrations_count ?? 0 }} orang</dd>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- 2. DESKRIPSI LOMBA --}}
    @if ($competition->description)
        @php
            $descLength = mb_strlen($competition->description);
            $isLong = $descLength > 400;
        @endphp
        <div class="siperlo-surface rounded-md p-6" x-data="{ expanded: {{ $isLong ? 'false' : 'true' }} }">
            <div class="flex items-center gap-2 mb-4">
                <x-lucide-file-text class="h-4 w-4 text-campus-green" aria-hidden="true" />
                <h3 class="font-display text-lg font-bold">Deskripsi Lomba</h3>
            </div>
            <div class="relative leading-7 text-ink/80"
                 :class="{ 'max-h-[200px] overflow-hidden': !expanded }">
                @php
                    $cleanDesc = preg_replace('/\n{3,}/', "\n\n", $competition->description);
                    $paragraphs = preg_split('/\n\n/', $cleanDesc);
                @endphp
                <div class="description-content">
                    @foreach ($paragraphs as $para)
                        @if (trim($para) !== '')
                            <p class="mb-3">{!! nl2br(e(trim($para))) !!}</p>
                        @endif
                    @endforeach
                </div>
                <div x-show="!expanded"
                     class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-white to-transparent">
                </div>
            </div>
            @if ($isLong)
                <button @click="expanded = !expanded"
                        class="mt-2 text-sm font-semibold text-campus-green hover:underline focus:outline-none"
                        x-text="expanded ? '⬆ Sembunyikan' : '⬇ Baca Selengkapnya'">
                </button>
            @endif
        </div>
    @endif

    {{-- 3. STATUS PENDAFTARAN --}}
    <div class="siperlo-surface rounded-md p-5">
        <div class="flex items-center gap-2">
            <x-lucide-clipboard-check class="h-4 w-4 text-campus-green" aria-hidden="true" />
            <h3 class="font-display text-lg font-bold">Status Pendaftaran</h3>
        </div>
        @if ($registration)
            <dl class="mt-3 divide-y divide-border-line text-sm">
                <div class="flex items-baseline justify-between gap-3 py-2">
                    <dt class="text-muted-ink">Status utama</dt>
                    <dd class="font-semibold text-ink">{{ $registration->primaryStatusLabel() }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-3 py-2">
                    <dt class="text-muted-ink">Mentor opsional</dt>
                    <dd class="text-ink">{{ $mentorStatus }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-3 py-2">
                    <dt class="text-muted-ink">Dana opsional</dt>
                    <dd class="text-ink">{{ $registration->latestFundRequest ? ($supportStatusLabels[$registration->latestFundRequest->status] ?? $registration->latestFundRequest->status) : 'Belum diajukan' }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-3 py-2">
                    <dt class="text-muted-ink">Laporan hasil</dt>
                    <dd class="text-ink">{{ $registration->resultStatusLabel() }}</dd>
                </div>
                @if ($registration->result)
                    <div class="flex items-baseline justify-between gap-3 py-2">
                        <dt class="text-muted-ink">Capaian</dt>
                        <dd class="font-semibold text-ink">{{ $registration->result }}</dd>
                    </div>
                @endif
            </dl>
            <a href="{{ route('registrations.index') }}" class="siperlo-btn-primary mt-4 block px-4 py-2 text-center text-sm">Lihat Progress</a>
        @elseif ($competition->isRegistrable() && auth()->user()->isRole('mahasiswa'))
            <form method="POST" action="{{ route('competitions.register', $competition) }}" class="mt-3">
                @csrf
                <x-submit-button label="Daftar Sekarang" pending-label="Mendaftarkan..." class="w-full" />
            </form>
        @else
            <p class="mt-3 text-sm text-ink/80">
                @if (! auth()->user()->isRole('mahasiswa'))
                    Pendaftaran lomba hanya tersedia untuk akun mahasiswa.
                @elseif ($isClosed)
                    Pendaftaran sudah ditutup.
                @else
                    Pendaftaran belum dibuka.
                @endif
            </p>
        @endif
    </div>

    {{-- 4. DOKUMEN & LINK RESMI --}}
    @if ($guidebookUrl || $competition->external_registration_url || $competition->official_website || $competition->social_media || ($competition->is_scraped && $competition->source_url))
        <div class="siperlo-surface rounded-md p-5">
            <div class="flex items-center gap-2">
                <x-lucide-link class="h-4 w-4 text-campus-green" aria-hidden="true" />
                <h3 class="font-display text-lg font-bold">Dokumen & Link Resmi</h3>
            </div>
            <div class="mt-4 space-y-3">
                @if ($guidebookUrl)
                    <a href="{{ $guidebookUrl }}" target="_blank" rel="noopener" class="{{ $documentsRedam ? 'siperlo-btn-secondary' : 'siperlo-btn-primary' }} flex items-center justify-center gap-2 px-4 py-2 text-sm">
                        <x-lucide-file-text class="h-4 w-4" aria-hidden="true" />
                        Lihat Guidebook
                    </a>
                @endif

                @if ($competition->external_registration_url)
                    <a href="{{ $competition->external_registration_url }}" target="_blank" rel="noopener" class="siperlo-btn-secondary flex items-center justify-center gap-2 px-4 py-2 text-sm">
                        <x-lucide-globe class="h-4 w-4" aria-hidden="true" />
                        Pendaftaran Lomba
                    </a>
                @endif
                @if ($competition->official_website)
                    <a href="{{ $competition->official_website }}" target="_blank" rel="noopener" class="siperlo-btn-secondary flex items-center justify-center gap-2 px-4 py-2 text-sm">
                        <x-lucide-external-link class="h-4 w-4" aria-hidden="true" />
                        Panduan Lomba
                    </a>
                @endif
                @if ($competition->is_scraped && $competition->source_url)
                    <a href="{{ $competition->source_url }}" target="_blank" rel="noopener"
                       class="flex items-center justify-center gap-2 rounded-md border-2 border-dashed border-emerald-300 bg-emerald-50/50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        Lihat di InfoLomba.id
                    </a>
                @endif
            </div>
        </div>
    @endif

</div>
@endsection
