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
            <div class="flex items-center justify-center bg-soft-green p-4 cursor-pointer" @click="$dispatch('open-poster')" title="Klik untuk memperbesar">
                <img src="{{ $posterUrl }}"
                     alt="Poster lomba {{ $competition->title }}"
                     loading="lazy"
                     decoding="async"
                     class="w-full rounded-md border border-border-line object-contain transition-transform duration-200 hover:scale-[1.02]"
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
                        x-text="expanded ? 'Sembunyikan' : 'Baca Selengkapnya'">
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
                    <dt class="text-muted-ink">Bukti pendaftaran</dt>
                    <dd>
                        @if ($registration->isProofVerified())
                            <span class="siperlo-status siperlo-status-success">Terverifikasi</span>
                        @elseif ($registration->isProofPending())
                            <span class="siperlo-status siperlo-status-warning">Menunggu Verifikasi</span>
                        @elseif ($registration->isProofRejected())
                            <span class="siperlo-status siperlo-status-danger">Ditolak</span>
                        @else
                            <span class="siperlo-status siperlo-status-neutral">Belum Diupload</span>
                        @endif
                    </dd>
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

            {{-- Proof missing/rejected: show admin notes + upload form --}}
            @if ($registration->canUploadProof())
                <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-4">
                    @if ($registration->isProofRejected() && $registration->proof_admin_notes)
                        <div class="text-sm text-red-800">
                            <span class="font-semibold">Alasan penolakan:</span> {{ $registration->proof_admin_notes }}
                        </div>
                    @elseif (! $registration->registration_proof_file)
                        <div class="text-sm text-red-800">
                            <span class="font-semibold">Bukti belum tersedia.</span> Upload bukti pendaftaran agar admin dapat memverifikasi dan mengubah status menjadi Berlangsung.
                        </div>
                    @endif
                    <form method="POST" action="{{ route('registrations.reupload-proof', $registration) }}" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        @method('PATCH')
                        <label class="text-sm font-semibold text-red-800">{{ $registration->isProofRejected() ? 'Upload ulang bukti pendaftaran' : 'Upload bukti pendaftaran' }}</label>
                        <label for="reupload-proof-{{ $registration->id }}" class="mt-2 flex cursor-pointer flex-col items-center rounded-lg border-2 border-dashed border-red-300 bg-red-50/50 px-4 py-5 transition hover:border-red-400 hover:bg-red-100/50">
                            <x-lucide-upload-cloud class="h-7 w-7 text-red-400" aria-hidden="true" />
                            <span class="mt-1 text-sm font-semibold text-red-800">Klik untuk pilih file</span>
                            <span class="mt-1 text-xs text-red-600">JPG, PNG, atau PDF — maks 2MB</span>
                            <span id="reupload-file-name-{{ $registration->id }}" class="mt-2 hidden rounded-md bg-red-100 px-3 py-1 text-xs font-semibold text-red-700"></span>
                        </label>
                        <input id="reupload-proof-{{ $registration->id }}" type="file" name="registration_proof_file" accept=".jpg,.jpeg,.png,.pdf" required class="sr-only"
                               onchange="const n=document.getElementById('reupload-file-name-{{ $registration->id }}');if(this.files[0]){n.textContent=this.files[0].name;n.classList.remove('hidden')}else{n.classList.add('hidden')}">
                        <x-submit-button label="{{ $registration->isProofRejected() ? 'Upload Ulang' : 'Upload Bukti' }}" pending-label="Mengupload..." class="mt-3 w-full" />
                    </form>
                </div>
            @elseif ($registration->isProofPending())
                <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    <x-lucide-clock class="mr-1 inline-block h-4 w-4" aria-hidden="true" />
                    Bukti pendaftaran kamu sedang menunggu verifikasi dari admin.
                </div>
            @endif

            <a href="{{ route('registrations.index') }}" class="siperlo-btn-primary mt-4 block px-4 py-2 text-center text-sm">Lihat Progress</a>
        @elseif ($competition->isRegistrable() && auth()->user()->isRole('mahasiswa'))
            <form method="POST" action="{{ route('competitions.register', $competition) }}" enctype="multipart/form-data" class="mt-3">
                @csrf
                <div class="mb-4">
                    <label class="text-sm font-semibold text-ink">
                        Bukti Pendaftaran Eksternal <span class="siperlo-required">*</span>
                    </label>
                    <p class="mt-1 text-xs text-muted-ink">
                        Upload screenshot konfirmasi pendaftaran, email konfirmasi, atau bukti nomor peserta dari situs penyelenggara.
                    </p>
                    <label for="registration-proof" class="mt-2 flex cursor-pointer flex-col items-center rounded-lg border-2 border-dashed border-border-line bg-panel/50 px-4 py-6 transition hover:border-campus-green hover:bg-campus-green/5">
                        <x-lucide-upload-cloud class="h-8 w-8 text-muted-ink" aria-hidden="true" />
                        <span class="mt-2 text-sm font-semibold text-ink">Klik untuk pilih file</span>
                        <span class="mt-1 text-xs text-muted-ink">JPG, PNG, atau PDF — maks 2MB</span>
                        <span id="proof-file-name" class="mt-2 hidden rounded-md bg-campus-green/10 px-3 py-1 text-xs font-semibold text-campus-green"></span>
                    </label>
                    <input id="registration-proof" type="file" name="registration_proof_file" accept=".jpg,.jpeg,.png,.pdf" required class="sr-only"
                           onchange="const n=document.getElementById('proof-file-name');if(this.files[0]){n.textContent=this.files[0].name;n.classList.remove('hidden')}else{n.classList.add('hidden')}">
                    @error('registration_proof_file')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
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

@push('modals')
    {{-- Fullscreen Poster Modal --}}
    <div x-data="{ open: false }"
         x-show="open" 
         @open-poster.window="open = true"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         @keydown.escape.window="open = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 cursor-pointer"
         style="display: none;"
         x-cloak>
         
         <div class="siperlo-surface w-full max-w-md rounded-lg shadow-xl overflow-hidden cursor-default" @click.stop>
             <!-- Modal Header -->
             <div class="flex items-center justify-between border-b border-border-line px-4 py-3 bg-panel">
                 <h3 class="font-display font-bold text-ink">Poster Lomba</h3>
                 <button @click="open = false" class="text-muted-ink hover:text-ink transition-colors cursor-pointer" aria-label="Tutup">
                     <x-lucide-x class="h-5 w-5" />
                 </button>
             </div>
             
             <!-- Modal Content -->
             <div class="flex items-center justify-center bg-soft-green p-4">
                 <img src="{{ $posterUrl }}" 
                      alt="Poster lomba {{ $competition->title }}" 
                      class="max-h-[550px] max-w-full object-contain rounded border border-border-line select-none">
             </div>
         </div>
    </div>
@endpush
