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
        ? (str_starts_with($competition->poster_image, 'http') ? $competition->poster_image : \Illuminate\Support\Facades\Storage::url($competition->poster_image))
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
            <div class="grid grid-cols-[240px_1fr]">
                <div class="flex items-center justify-center bg-soft-green p-4 cursor-pointer" @click="$dispatch('open-poster')" title="Klik untuk memperbesar">
                    <img src="{{ $posterUrl }}"
                         alt="Poster atau ilustrasi lomba {{ $competition->title }}"
                         decoding="async"
                         class="w-full rounded-md border border-border-line object-contain transition-transform duration-200 hover:scale-[1.02]"
                         style="max-height: 340px;">
                </div>
                <div class="flex flex-col p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-muted-ink">{{ $competition->category }} - {{ $competition->type ?: 'Umum' }}</div>
                            <h2 class="mt-1 font-display text-2xl font-bold">{{ $competition->title }}</h2>
                        </div>
                        <span class="{{ $statusClasses[$competition->displayStatus()] ?? 'siperlo-status siperlo-status-neutral' }}">
                            {{ $statusLabels[$competition->displayStatus()] ?? ucfirst($competition->displayStatus()) }}
                        </span>
                    </div>

                    <dl class="mt-5 grid grid-cols-2 gap-x-6 gap-y-3 border-t border-border-line pt-5 text-sm">
                        <div class="flex items-start gap-2">
                            <x-lucide-calendar class="mt-0.5 h-4 w-4 shrink-0 text-campus-green" aria-hidden="true" />
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-muted-ink">Deadline</dt>
                                <dd class="font-semibold text-ink">{{ $competition->registration_deadline->translatedFormat('d M Y H:i') }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-lucide-calendar class="mt-0.5 h-4 w-4 shrink-0 text-campus-green" aria-hidden="true" />
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-muted-ink">Pelaksanaan</dt>
                                <dd class="font-semibold text-ink">{{ optional($competition->event_start)->translatedFormat('d M Y') ?: '-' }} sampai {{ optional($competition->event_end)->translatedFormat('d M Y') ?: '-' }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-lucide-wallet class="mt-0.5 h-4 w-4 shrink-0 text-campus-green" aria-hidden="true" />
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-muted-ink">Biaya</dt>
                                <dd class="font-semibold text-ink">{{ $competition->fee > 0 ? 'Rp '.number_format($competition->fee, 0, ',', '.') : 'Gratis' }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-lucide-users class="mt-0.5 h-4 w-4 shrink-0 text-campus-green" aria-hidden="true" />
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-muted-ink">Pendaftar</dt>
                                <dd class="font-semibold text-ink">{{ $competition->registrations->count() }} mahasiswa</dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Description Card --}}
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

</div>
@endsection
