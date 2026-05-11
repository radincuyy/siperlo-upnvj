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
        ? \Illuminate\Support\Facades\Storage::url($competition->poster_image)
        : asset('brand/siperlo-campus.svg');
    $guidebookUrl = $competition->guidebook_file
        ? \Illuminate\Support\Facades\Storage::url($competition->guidebook_file)
        : null;
    $requirements = collect(preg_split('/\r\n|\r|\n/', (string) $competition->requirements))->map(fn ($line) => trim($line))->filter();
    $benefits = collect(preg_split('/\r\n|\r|\n/', (string) $competition->benefits))->map(fn ($line) => trim($line))->filter();
    $timeline = collect(preg_split('/\r\n|\r|\n/', (string) $competition->timeline))->map(fn ($line) => trim($line))->filter();
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
    $competitionStatusClass = match ($competition->status) {
        'open' => 'siperlo-status siperlo-status-success',
        'soon' => 'siperlo-status siperlo-status-neutral',
        'closed' => 'siperlo-status siperlo-status-warning',
        default => 'siperlo-status siperlo-status-neutral',
    };
    $statusLabel = $competition->status === 'open' ? 'Pendaftaran Buka' : ($competition->status === 'soon' ? 'Akan Datang' : 'Ditutup');
    $feeLabel = $competition->fee > 0 ? 'Rp '.number_format($competition->fee, 0, ',', '.') : 'Gratis';
@endphp

<div class="grid gap-6 xl:grid-cols-[1fr_360px]">
    <section class="space-y-5">
        <div class="siperlo-surface overflow-hidden rounded-md">
            <div class="grid lg:grid-cols-[320px_1fr]">
                <div class="bg-soft-green p-4">
                    <img src="{{ $posterUrl }}"
                         alt="Poster atau ilustrasi lomba {{ $competition->title }}"
                         decoding="async"
                         class="aspect-[4/5] w-full rounded-md border border-border-line object-cover">
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold uppercase tracking-[0.14em] text-muted-ink">{{ $competition->category }} · {{ $competition->type ?: 'Umum' }}</div>
                            <h2 class="mt-2 font-display text-2xl font-bold leading-tight">{{ $competition->title }}</h2>
                            <div class="mt-1 text-sm text-muted-ink">{{ $competition->organizer }}</div>
                        </div>
                        <span class="{{ $competitionStatusClass }} shrink-0">{{ $statusLabel }}</span>
                    </div>

                    @if ($competition->description)
                        <p class="mt-5 leading-7 text-ink/80">{{ $competition->description }}</p>
                    @endif

                    <dl class="mt-6 grid gap-3 text-sm text-ink/80 sm:grid-cols-2">
                        <div class="flex items-start gap-2">
                            <x-lucide-calendar class="mt-0.5 h-4 w-4 shrink-0 text-muted-ink" aria-hidden="true" />
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-muted-ink">Deadline</dt>
                                <dd class="font-semibold text-ink">{{ $competition->registration_deadline->translatedFormat('d M Y H:i') }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-lucide-calendar-range class="mt-0.5 h-4 w-4 shrink-0 text-muted-ink" aria-hidden="true" />
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-muted-ink">Pelaksanaan</dt>
                                <dd class="font-semibold text-ink">{{ optional($competition->event_start)->translatedFormat('d M Y') ?: '-' }} — {{ optional($competition->event_end)->translatedFormat('d M Y') ?: '-' }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-lucide-map-pin class="mt-0.5 h-4 w-4 shrink-0 text-muted-ink" aria-hidden="true" />
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-muted-ink">Lokasi</dt>
                                <dd class="font-semibold text-ink">{{ $competition->location ?: '-' }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-lucide-wallet class="mt-0.5 h-4 w-4 shrink-0 text-muted-ink" aria-hidden="true" />
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-muted-ink">Biaya</dt>
                                <dd class="font-semibold text-ink">{{ $feeLabel }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-lucide-building-2 class="mt-0.5 h-4 w-4 shrink-0 text-muted-ink" aria-hidden="true" />
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-muted-ink">Penyelenggara</dt>
                                <dd class="font-semibold text-ink">{{ $competition->organizer }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-lucide-users class="mt-0.5 h-4 w-4 shrink-0 text-muted-ink" aria-hidden="true" />
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-muted-ink">Pendaftar</dt>
                                <dd class="font-semibold text-ink">{{ $competition->registrations_count ?? 0 }} orang</dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <div class="siperlo-surface rounded-md p-6">
                <div class="flex items-center gap-2">
                    <x-lucide-list-checks class="h-4 w-4 text-campus-green" aria-hidden="true" />
                    <h2 class="font-display text-xl font-bold">Syarat Peserta</h2>
                </div>
                <div class="mt-4 space-y-3 text-sm text-ink/80">
                    @forelse ($requirements as $item)
                        <div class="flex gap-3">
                            <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-campus-green" aria-hidden="true"></span>
                            <span>{{ $item }}</span>
                        </div>
                    @empty
                        <p>Belum ada syarat khusus. Ikuti ketentuan pada guidebook atau informasi resmi penyelenggara.</p>
                    @endforelse
                </div>
            </div>

            <div class="siperlo-surface rounded-md p-6">
                <div class="flex items-center gap-2">
                    <x-lucide-gift class="h-4 w-4 text-campus-gold" aria-hidden="true" />
                    <h2 class="font-display text-xl font-bold">Benefit & Hadiah</h2>
                </div>
                <div class="mt-4 space-y-3 text-sm text-ink/80">
                    @forelse ($benefits as $item)
                        <div class="flex gap-3">
                            <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-campus-gold" aria-hidden="true"></span>
                            <span>{{ $item }}</span>
                        </div>
                    @empty
                        <p>Benefit mengikuti lomba akan mengikuti informasi resmi penyelenggara.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="siperlo-surface rounded-md p-6">
            <div class="flex items-center gap-2">
                <x-lucide-clock class="h-4 w-4 text-campus-green" aria-hidden="true" />
                <h2 class="font-display text-xl font-bold">Timeline Lomba</h2>
            </div>
            <div class="mt-5 space-y-3">
                @forelse ($timeline as $item)
                    <div class="flex gap-4 rounded-md border border-border-line bg-admin-note-surface p-3 text-sm">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-campus-green text-xs font-bold text-white" aria-hidden="true">{{ $loop->iteration }}</span>
                        <span class="pt-1 text-ink/80">{{ $item }}</span>
                    </div>
                @empty
                    <div class="rounded-md border border-border-line bg-admin-note-surface p-3 text-sm text-ink/80">
                        Jadwal utama: pendaftaran ditutup {{ $competition->registration_deadline->translatedFormat('d F Y') }} dan pelaksanaan mulai {{ optional($competition->event_start)->translatedFormat('d F Y') ?: 'menunggu informasi resmi' }}.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <aside class="space-y-5">
        <div class="siperlo-surface rounded-md p-5">
            <div class="flex items-center gap-2">
                <x-lucide-clipboard-check class="h-4 w-4 text-campus-green" aria-hidden="true" />
                <h2 class="font-display text-lg font-bold">Status Pendaftaran</h2>
            </div>
            @if ($registration)
                <div class="mt-3 rounded-md bg-admin-note-surface p-3 text-sm">
                    <div class="font-semibold">{{ $registration->primaryStatusLabel() }}</div>
                    <div class="mt-1 text-ink/80">Mentor opsional: {{ $mentorStatus }}</div>
                    <div class="text-ink/80">Dana opsional: {{ $registration->latestFundRequest ? ($supportStatusLabels[$registration->latestFundRequest->status] ?? $registration->latestFundRequest->status) : 'Belum diajukan' }}</div>
                    <div class="text-ink/80">Laporan hasil: {{ $registration->resultStatusLabel() }}</div>
                    @if ($registration->result)
                        <div class="mt-2 font-semibold text-ink">{{ $registration->result }}</div>
                    @endif
                </div>
                <a href="{{ route('registrations.index') }}" class="siperlo-btn-primary mt-4 block px-4 py-2 text-center text-sm">Lihat Progress</a>
            @elseif (auth()->user()->isRole('mahasiswa') && $competition->status === 'open' && ! $competition->registration_deadline->isPast())
                <form method="POST" action="{{ route('competitions.register', $competition) }}" class="mt-3">
                    @csrf
                    <x-submit-button label="Daftar Sekarang" pending-label="Mendaftarkan..." class="w-full" />
                </form>
            @else
                <p class="mt-3 text-sm text-ink/80">Pendaftaran tidak tersedia untuk akun atau status lomba saat ini.</p>
            @endif
        </div>

        <div class="siperlo-surface rounded-md p-5">
            <div class="flex items-center gap-2">
                <x-lucide-link class="h-4 w-4 text-campus-green" aria-hidden="true" />
                <h2 class="font-display text-lg font-bold">Dokumen & Link Resmi</h2>
            </div>
            <div class="mt-4 space-y-3">
                @if ($guidebookUrl)
                    <a href="{{ $guidebookUrl }}" target="_blank" rel="noopener" class="siperlo-btn-primary flex items-center justify-center gap-2 px-4 py-2 text-sm">
                        <x-lucide-file-text class="h-4 w-4" aria-hidden="true" />
                        Lihat Guidebook
                    </a>
                @else
                    <div class="rounded-md bg-admin-note-surface p-3 text-sm text-ink/80">Guidebook belum diunggah.</div>
                @endif

                @if ($competition->external_registration_url)
                    <a href="{{ $competition->external_registration_url }}" target="_blank" rel="noopener" class="siperlo-btn-secondary flex items-center justify-center gap-2 px-4 py-2 text-sm">
                        <x-lucide-external-link class="h-4 w-4" aria-hidden="true" />
                        Pendaftaran Eksternal
                    </a>
                @endif
                @if ($competition->official_website)
                    <a href="{{ $competition->official_website }}" target="_blank" rel="noopener" class="siperlo-btn-secondary flex items-center justify-center gap-2 px-4 py-2 text-sm">
                        <x-lucide-globe class="h-4 w-4" aria-hidden="true" />
                        Website Resmi
                    </a>
                @endif
                @if ($competition->social_media)
                    <a href="{{ $competition->social_media }}" target="_blank" rel="noopener" class="siperlo-btn-secondary flex items-center justify-center gap-2 px-4 py-2 text-sm">
                        <x-lucide-share-2 class="h-4 w-4" aria-hidden="true" />
                        Sosial Media
                    </a>
                @endif
            </div>
        </div>

        <div class="siperlo-surface rounded-md p-5">
            <div class="flex items-center gap-2">
                <x-lucide-phone class="h-4 w-4 text-campus-green" aria-hidden="true" />
                <h2 class="font-display text-lg font-bold">Contact Person</h2>
            </div>
            <div class="mt-4 rounded-md bg-admin-note-surface p-3 text-sm">
                <div class="font-semibold text-ink">{{ $competition->contact_person_name ?: 'Belum tersedia' }}</div>
                @if ($competition->contact_person_phone)
                    <div class="mt-1 text-ink/80">WA: {{ $competition->contact_person_phone }}</div>
                @endif
                @if ($competition->contact_person_email)
                    <div class="text-ink/80">Email: {{ $competition->contact_person_email }}</div>
                @endif
            </div>
        </div>
    </aside>
</div>
@endsection
