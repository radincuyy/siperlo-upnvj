@extends('layouts.siperlo')

@section('title', 'Daftar Lomba - SIPERLO')
@section('eyebrow', 'Mahasiswa')
@section('page_title', 'Daftar Lomba')

@section('content')
@php
    $hasFilter = filled(request('search')) || (request('category') && request('category') !== 'all') || (request('type') && request('type') !== 'all');
    $totalResults = $competitions->total();
@endphp

<div class="grid gap-6 xl:grid-cols-[1fr_320px]">
    <section>
        <form method="GET" action="{{ route('competitions.index') }}" class="siperlo-surface rounded-md p-4">
            <div class="grid gap-3 lg:grid-cols-[1fr_180px_180px_auto]">
                <div>
                    <label for="competition-search" class="text-sm font-semibold">Cari lomba</label>
                    <input id="competition-search" name="search" value="{{ request('search') }}" placeholder="Nama lomba, penyelenggara, kategori..."
                           class="siperlo-field mt-1 w-full">
                </div>
                <div>
                    <label for="competition-category" class="text-sm font-semibold">Kategori</label>
                    <select id="competition-category" name="category" class="siperlo-field mt-1 w-full">
                        <option value="all">Semua</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="competition-type" class="text-sm font-semibold">Tipe</label>
                    <select id="competition-type" name="type" class="siperlo-field mt-1 w-full">
                        <option value="all">Semua</option>
                        @foreach ($types as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button class="siperlo-btn-primary px-4 py-2 text-sm">Terapkan Filter</button>
                    @if ($hasFilter)
                        <a href="{{ route('competitions.index') }}" class="siperlo-btn-secondary px-4 py-2 text-sm">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="mt-4 flex items-center gap-2 text-sm text-muted-ink">
            <x-lucide-list-filter class="h-4 w-4" aria-hidden="true" />
            <span>{{ $totalResults }} lomba ditampilkan{{ $hasFilter ? ' dari filter yang dipilih' : '' }}.</span>
        </div>

        <div class="mt-5 grid gap-5 lg:grid-cols-2">
            @forelse ($competitions as $competition)
                @php
                    $registered = in_array($competition->id, $registeredCompetitionIds, true);
                    $posterUrl = $competition->poster_image
                        ? \Illuminate\Support\Facades\Storage::url($competition->poster_image)
                        : asset('brand/siperlo-mark.png');
                    $displayStatus = $competition->displayStatus();
                    $statusClass = match ($displayStatus) {
                        'open' => 'siperlo-status siperlo-status-success',
                        'soon' => 'siperlo-status siperlo-status-neutral',
                        'closed' => 'siperlo-status siperlo-status-warning',
                        default => 'siperlo-status siperlo-status-neutral',
                    };
                    $statusLabel = match ($displayStatus) {
                        'open' => 'Pendaftaran Buka',
                        'soon' => 'Akan Datang',
                        default => 'Ditutup',
                    };
                    $feeLabel = $competition->fee > 0 ? 'Rp '.number_format($competition->fee, 0, ',', '.') : 'Gratis';
                @endphp
                <article class="siperlo-surface rounded-md p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-start gap-4">
                            <div class="flex h-36 w-24 shrink-0 overflow-hidden rounded-md border border-border-line bg-admin-note-surface">
                                <img src="{{ $posterUrl }}"
                                     alt="{{ $competition->poster_image ? 'Poster lomba '.$competition->title : '' }}"
                                     @if (! $competition->poster_image) aria-hidden="true" @endif
                                     loading="lazy"
                                     decoding="async"
                                     class="{{ $competition->poster_image ? 'h-full w-full object-cover' : 'm-auto h-12 w-12 object-contain' }}">
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-semibold uppercase tracking-[0.14em] text-muted-ink">{{ $competition->category }} · {{ $competition->type ?: 'Umum' }}</div>
                                <h3 class="mt-2 font-display text-2xl font-bold leading-tight">{{ $competition->title }}</h3>
                            </div>
                        </div>
                        <span class="{{ $statusClass }} shrink-0">{{ $statusLabel }}</span>
                    </div>

                    <dl class="mt-5 grid gap-3 text-sm text-ink/80 sm:grid-cols-2">
                        <div class="flex items-start gap-2">
                            <x-lucide-calendar class="mt-0.5 h-4 w-4 shrink-0 text-muted-ink" aria-hidden="true" />
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-muted-ink">Deadline</dt>
                                <dd class="font-semibold text-ink">{{ $competition->registration_deadline->translatedFormat('d M Y') }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-lucide-users class="mt-0.5 h-4 w-4 shrink-0 text-muted-ink" aria-hidden="true" />
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-muted-ink">Pendaftar</dt>
                                <dd class="font-semibold text-ink">{{ $competition->registrations_count }} orang</dd>
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
                            <x-lucide-wallet class="mt-0.5 h-4 w-4 shrink-0 text-muted-ink" aria-hidden="true" />
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-muted-ink">Biaya</dt>
                                <dd class="font-semibold text-ink">{{ $feeLabel }}</dd>
                            </div>
                        </div>
                    </dl>

                    <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-border-line pt-4">
                        <a href="{{ route('competitions.show', $competition) }}" class="siperlo-btn-secondary px-4 py-2 text-sm">
                            Lihat Detail
                        </a>

                        @if ($registered)
                            <span class="siperlo-status siperlo-status-success">Sudah Terdaftar</span>
                        @elseif (auth()->user()->isRole('mahasiswa') && $competition->status === 'open' && ! $competition->registration_deadline->isPast())
                            <form method="POST" action="{{ route('competitions.register', $competition) }}">
                                @csrf
                                <x-submit-button label="Daftar Sekarang" pending-label="Mendaftarkan..." />
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="siperlo-surface rounded-md p-8 text-center text-muted-ink lg:col-span-2">
                    <img src="{{ asset('brand/siperlo-empty.svg') }}" alt="" aria-hidden="true" loading="lazy" decoding="async" class="mx-auto mb-4 h-32 w-auto">
                    <h3 class="font-display text-xl font-bold text-ink">Belum ada lomba yang cocok.</h3>
                    <p class="mt-2 text-sm">Coba ubah kata kunci atau filter pencarian.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-5">{{ $competitions->links() }}</div>
    </section>

    <aside class="space-y-5">
        <div class="siperlo-surface rounded-md p-5">
            <div class="flex items-center gap-2">
                <x-lucide-alarm-clock class="h-4 w-4 text-amber-700" aria-hidden="true" />
                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Prioritas</span>
            </div>
            <h2 class="mt-2 font-display text-xl font-bold">Mendekati Deadline</h2>
            <div class="mt-4 space-y-3">
                @forelse ($upcomingDeadlines as $competition)
                    @php
                        $daysRemaining = (int) ceil(max(0, now()->diffInDays($competition->registration_deadline, false)));
                    @endphp
                    <a href="{{ route('competitions.show', $competition) }}" class="block rounded-md border border-amber-200 bg-amber-50 p-3 text-sm transition hover:bg-amber-100">
                        <div class="font-semibold text-ink">{{ $competition->title }}</div>
                        <div class="mt-1 text-amber-800">{{ $daysRemaining }} hari lagi</div>
                    </a>
                @empty
                    <p class="text-sm text-muted-ink">Belum ada deadline terdekat.</p>
                @endforelse
            </div>
        </div>

        <div class="siperlo-surface rounded-md p-5">
            <div class="flex items-center gap-2">
                <x-lucide-map class="h-4 w-4 text-campus-green" aria-hidden="true" />
                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-campus-green">Panduan</span>
            </div>
            <h2 class="mt-2 font-display text-xl font-bold">Alur Pendaftaran</h2>
            <p class="mt-2 text-sm text-ink/80">Lengkapi profil, pilih lomba, ikuti kompetisi, lalu laporkan hasil.</p>
            <a href="{{ route('sop.index') }}" class="siperlo-btn-secondary mt-4 block px-4 py-2 text-center text-sm">Baca SOP Lengkap</a>
        </div>
    </aside>
</div>
@endsection
