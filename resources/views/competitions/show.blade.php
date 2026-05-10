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
@endphp

<div class="grid gap-6 xl:grid-cols-[1fr_360px]">
    <section class="space-y-5">
        <div class="siperlo-surface overflow-hidden rounded-md">
            <div class="grid lg:grid-cols-[360px_1fr]">
                <div class="bg-soft-green p-4">
                    <img src="{{ $posterUrl }}"
                         alt="Poster atau ilustrasi lomba {{ $competition->title }}"
                         decoding="async"
                         class="aspect-[4/5] w-full rounded-md border border-border-line object-cover">
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm font-semibold text-muted-ink">
                        <span>{{ $competition->category }}</span>
                        <span aria-hidden="true" class="text-border-line">·</span>
                        <span>{{ $competition->type ?: 'Umum' }}</span>
                        <span aria-hidden="true" class="text-border-line">·</span>
                        <span>{{ $competition->organizer }}</span>
                        <span class="{{ $competitionStatusClass }} ml-auto">{{ $statusLabel }}</span>
                    </div>
                    <p class="mt-5 leading-7 text-ink/80">{{ $competition->description }}</p>

                    <dl class="mt-6 grid gap-4 rounded-md bg-admin-note-surface p-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-semibold uppercase text-muted-ink">Deadline</dt>
                            <dd class="mt-1 font-semibold">{{ $competition->registration_deadline->translatedFormat('d F Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-muted-ink">Pelaksanaan</dt>
                            <dd class="mt-1 font-semibold">{{ optional($competition->event_start)->translatedFormat('d M Y') ?: '-' }} - {{ optional($competition->event_end)->translatedFormat('d M Y') ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-muted-ink">Lokasi</dt>
                            <dd class="mt-1 font-semibold">{{ $competition->location ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-muted-ink">Biaya</dt>
                            <dd class="mt-1 font-semibold">{{ $competition->fee > 0 ? 'Rp '.number_format($competition->fee, 0, ',', '.') : 'Gratis' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <div class="siperlo-surface rounded-md p-6">
                <h2 class="font-display text-xl font-bold">Syarat Peserta</h2>
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
                <h2 class="font-display text-xl font-bold">Benefit & Hadiah</h2>
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
            <h2 class="font-display text-xl font-bold">Timeline Lomba</h2>
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
            <h2 class="font-display text-lg font-bold">Status Pendaftaran</h2>
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
            <h2 class="font-display text-lg font-bold">Dokumen & Link Resmi</h2>
            <div class="mt-4 space-y-3">
                @if ($guidebookUrl)
                    <a href="{{ $guidebookUrl }}" target="_blank" rel="noopener" class="siperlo-btn-primary block px-4 py-2 text-center text-sm">Lihat Guidebook</a>
                @else
                    <div class="rounded-md bg-admin-note-surface p-3 text-sm text-ink/80">Guidebook belum diunggah.</div>
                @endif

                @if ($competition->external_registration_url)
                    <a href="{{ $competition->external_registration_url }}" target="_blank" rel="noopener" class="siperlo-btn-secondary block px-4 py-2 text-center text-sm">Pendaftaran Eksternal</a>
                @endif
                @if ($competition->official_website)
                    <a href="{{ $competition->official_website }}" target="_blank" rel="noopener" class="siperlo-btn-secondary block px-4 py-2 text-center text-sm">Website Resmi</a>
                @endif
                @if ($competition->social_media)
                    <a href="{{ $competition->social_media }}" target="_blank" rel="noopener" class="siperlo-btn-secondary block px-4 py-2 text-center text-sm">Sosial Media</a>
                @endif
            </div>
        </div>

        <div class="siperlo-surface rounded-md p-5">
            <h2 class="font-display text-lg font-bold">Contact Person</h2>
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
