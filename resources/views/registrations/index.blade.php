@extends('layouts.siperlo')

@section('title', 'Lomba Saya - SIPERLO')
@section('eyebrow', 'Mahasiswa')
@section('page_title', 'Lomba Saya')

@section('content')
@php
    $supportStatusLabels = [
        'pending' => 'Menunggu review',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'revision' => 'Perlu revisi',
    ];

    $totalCount = $registrations->count();
    $monitoringCount = $registrations->reject(fn ($registration) => $registration->primaryStatus() === 'finished')->count();
    $actionCount = $registrations->where('result_status', 'revision')->count();
    $finishedCount = $registrations->filter(fn ($registration) => $registration->primaryStatus() === 'finished')->count();
@endphp

@if ($totalCount > 0)
    <div class="siperlo-surface flex flex-wrap items-center gap-x-6 gap-y-2 rounded-md p-4 text-sm text-ink/80">
        <span>Kamu mengikuti <span class="font-semibold text-ink">{{ $totalCount }} lomba</span>.</span>
        <span class="inline-flex items-center gap-1.5">
            <x-lucide-activity class="h-4 w-4 text-muted-ink" aria-hidden="true" />
            <span class="font-semibold text-ink">{{ $monitoringCount }}</span> monitoring
        </span>
        <span class="inline-flex items-center gap-1.5 {{ $actionCount > 0 ? 'text-amber-800' : '' }}">
            <x-lucide-triangle-alert class="h-4 w-4 {{ $actionCount > 0 ? 'text-amber-700' : 'text-muted-ink' }}" aria-hidden="true" />
            <span class="font-semibold {{ $actionCount > 0 ? 'text-amber-800' : 'text-ink' }}">{{ $actionCount }}</span> Perlu Tindakan
        </span>
        <span class="inline-flex items-center gap-1.5">
            <x-lucide-circle-check class="h-4 w-4 text-muted-ink" aria-hidden="true" />
            <span class="font-semibold text-ink">{{ $finishedCount }}</span> selesai
        </span>
    </div>
@endif

<div class="mt-5 space-y-5">
    @forelse ($registrations as $registration)
        @php
            $statusRank = $registration->primaryStatusRank();
            $latestMentorRequest = $registration->mentorRequests->sortByDesc('created_at')->first();
            $mentorStatus = $registration->mentor
                ? $registration->mentor->user->name
                : ($latestMentorRequest ? ($supportStatusLabels[$latestMentorRequest->status] ?? $latestMentorRequest->status) : 'Belum diajukan');
            $fundStatus = $registration->latestFundRequest
                ? ($supportStatusLabels[$registration->latestFundRequest->status] ?? $registration->latestFundRequest->status)
                : 'Belum diajukan';
            $mentorCaption = $registration->mentor
                ? 'Mentor aktif'
                : ($latestMentorRequest ? 'Status pengajuan mentor' : 'Opsional, belum digunakan');
            $fundCaption = $registration->latestFundRequest
                ? 'Status pengajuan dana'
                : 'Opsional, belum digunakan';
            $resultStatus = $registration->resultStatusLabel();
            $isFinished = $registration->primaryStatus() === 'finished';
            $hasResultReport = $registration->hasResultReport();
            $isResultPending = $registration->result_status === 'pending';
            $isResultRevision = $registration->result_status === 'revision';
            $showMentorAction = ! $hasResultReport && $registration->canRequestMentor();
            $showFundAction = ! $hasResultReport && $registration->canRequestFund();
            $showMentorSupport = $showMentorAction || $registration->mentor || $latestMentorRequest;
            $showFundSupport = $showFundAction || $registration->latestFundRequest;

            $resultBadgeClass = match ($registration->result_status) {
                'revision' => 'siperlo-status siperlo-status-warning',
                'pending' => 'siperlo-status siperlo-status-info',
                'approved' => 'siperlo-status siperlo-status-success',
                'rejected' => 'siperlo-status siperlo-status-danger',
                default => 'siperlo-status siperlo-status-neutral',
            };
            $nextAction = match (true) {
                $isFinished => [
                    'title' => $registration->result_status === 'approved' ? 'Hasil sudah divalidasi' : 'Proses lomba sudah ditutup',
                    'description' => 'Sudah masuk riwayat. Detail lomba dan laporan hasil masih bisa dibuka.',
                    'label' => null,
                    'badge' => null,
                ],
                $isResultPending => [
                    'title' => 'Laporan hasil sedang divalidasi',
                    'description' => 'Aksi mentor dan dana ditutup selama proses validasi.',
                    'label' => 'Menunggu admin',
                    'badge' => 'siperlo-status siperlo-status-info',
                ],
                $isResultRevision => [
                    'title' => 'Perbarui laporan hasil',
                    'description' => 'Baca catatan review, lalu kirim ulang capaian atau bukti yang sudah diperbaiki.',
                    'label' => 'Perlu tindakan',
                    'badge' => 'siperlo-status siperlo-status-warning',
                ],
                $registration->canReportResult() => [
                    'title' => 'Laporkan hasil saat lomba selesai',
                    'description' => 'Kirim capaian dan bukti untuk divalidasi admin.',
                    'label' => null,
                    'badge' => null,
                ],
                $registration->primaryStatus() === 'registered' => [
                    'title' => 'Pendaftaran sudah tercatat',
                    'description' => 'Mentor dan dana opsional.',
                    'label' => null,
                    'badge' => null,
                ],
                default => [
                    'title' => 'Status lomba sedang dimonitor',
                    'description' => 'Tunggu update status dari admin.',
                    'label' => null,
                    'badge' => null,
                ],
            };
            $mentorBadgeClass = match (true) {
                filled($registration->mentor_id) => 'siperlo-status siperlo-status-success',
                $latestMentorRequest?->status === 'pending' => 'siperlo-status siperlo-status-warning',
                $latestMentorRequest?->status === 'rejected' => 'siperlo-status siperlo-status-danger',
                default => 'siperlo-status siperlo-status-neutral',
            };
            $fundBadgeClass = match ($registration->latestFundRequest?->status) {
                'approved' => 'siperlo-status siperlo-status-success',
                'pending' => 'siperlo-status siperlo-status-warning',
                'rejected' => 'siperlo-status siperlo-status-danger',
                default => 'siperlo-status siperlo-status-neutral',
            };
            $posterUrl = $registration->competition->poster_image
                ? (str_starts_with($registration->competition->poster_image, 'http') ? $registration->competition->poster_image : \Illuminate\Support\Facades\Storage::url($registration->competition->poster_image))
                : asset('brand/siperlo-mark.png');
        @endphp
        <article class="siperlo-surface divide-y divide-border-line rounded-md">
            {{-- Header region --}}
            <div class="flex flex-wrap items-start justify-between gap-4 p-5">
                <div class="flex items-start gap-4">
                    <div class="flex h-20 w-14 shrink-0 overflow-hidden rounded-md border border-border-line bg-admin-note-surface">
                        <img src="{{ $posterUrl }}"
                             alt="{{ $registration->competition->poster_image ? 'Poster lomba '.$registration->competition->title : '' }}"
                             @if (! $registration->competition->poster_image) aria-hidden="true" @endif
                             loading="lazy"
                             decoding="async"
                             class="{{ $registration->competition->poster_image ? 'h-full w-full object-cover' : 'm-auto h-8 w-8 object-contain' }}">
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-semibold uppercase tracking-[0.14em] text-muted-ink">{{ $registration->competition->category }} · {{ $registration->competition->type }}</div>
                        <h2 class="mt-2 font-display text-2xl font-bold leading-tight lg:text-3xl">{{ $registration->competition->title }}</h2>
                        <div class="mt-2 text-sm text-ink/80">Status utama: <span class="font-semibold text-ink">{{ $registration->primaryStatusLabel() }}</span></div>
                    </div>
                </div>
                <span class="siperlo-pill px-3 py-1 text-xs">{{ $registration->primaryStatusLabel() }}</span>
            </div>

            {{-- Progress region --}}
            <div class="grid md:grid-cols-3">
                @foreach (\App\Models\Registration::PROGRESS_STEPS as $step)
                    @php
                        $reached = $loop->iteration <= $statusRank;
                        $isCurrent = $loop->iteration === $statusRank;
                    @endphp
                    <div class="flex items-center gap-3 border-b border-border-line px-5 py-3 text-sm font-semibold last:border-b-0 md:border-b-0 md:border-r md:last:border-r-0 {{ $reached ? 'bg-soft-green text-campus-green' : 'text-muted-ink' }}">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $reached ? 'bg-campus-green text-white' : 'border border-border-line bg-panel text-muted-ink' }}">
                            @if ($reached && ! $isCurrent)
                                <x-lucide-check class="h-4 w-4" aria-hidden="true" />
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </span>
                        <span>{{ $step }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Next action region --}}
            <div class="p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-base font-semibold text-ink">{{ $nextAction['title'] }}</div>
                        <p class="mt-1 max-w-3xl text-sm text-ink/75">{{ $nextAction['description'] }}</p>
                    </div>
                    @if ($nextAction['label'])
                        <span class="{{ $nextAction['badge'] }} shrink-0">{{ $nextAction['label'] }}</span>
                    @endif
                </div>
            </div>

            {{-- Support region (mentor / dana / laporan) --}}
            @php
                $supportRows = [];
                if ($showMentorSupport) {
                    $supportRows[] = [
                        'label' => 'Mentor',
                        'badge' => $mentorBadgeClass,
                        'badgeText' => $mentorStatus,
                        'caption' => $mentorCaption,
                    ];
                }
                if ($showFundSupport) {
                    $supportRows[] = [
                        'label' => 'Bantuan Dana',
                        'badge' => $fundBadgeClass,
                        'badgeText' => $fundStatus,
                        'caption' => $fundCaption,
                    ];
                }
            @endphp
            <dl class="divide-y divide-border-line">
                @foreach ($supportRows as $row)
                    <div class="grid gap-1 px-5 py-3 text-sm sm:grid-cols-[140px_1fr_auto] sm:items-center sm:gap-x-4">
                        <dt class="text-xs font-semibold uppercase text-muted-ink">{{ $row['label'] }}</dt>
                        <dd class="text-ink/80">{{ $row['caption'] }}</dd>
                        <dd class="justify-self-start sm:justify-self-end"><span class="{{ $row['badge'] }}">{{ $row['badgeText'] }}</span></dd>
                    </div>
                @endforeach
                <div class="px-5 py-3 text-sm">
                    <div class="grid gap-1 sm:grid-cols-[140px_1fr_auto] sm:items-center sm:gap-x-4">
                        <dt class="text-xs font-semibold uppercase text-muted-ink">Laporan Hasil</dt>
                        <dd class="text-ink/80">
                            @if ($isResultRevision)
                                Perlu diperbaiki sebelum admin bisa memvalidasi hasil.
                            @elseif ($isResultPending)
                                Laporan sudah dikirim dan sedang menunggu validasi admin.
                            @elseif (! $hasResultReport)
                                Belum ada laporan hasil dari mahasiswa.
                            @elseif ($registration->result)
                                Capaian: <span class="font-semibold text-ink">{{ $registration->result }}</span>
                            @endif
                        </dd>
                        <dd class="justify-self-start sm:justify-self-end"><span class="{{ $resultBadgeClass }}">{{ $resultStatus }}</span></dd>
                    </div>
                    @if ($registration->result_admin_notes)
                        <dd class="mt-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                            <span class="font-semibold">{{ $isResultRevision ? 'Catatan perbaikan:' : 'Catatan review hasil:' }}</span>
                            {{ $registration->result_admin_notes }}
                        </dd>
                    @endif
                </div>
            </dl>

            {{-- Actions region --}}
            <div class="flex flex-col gap-3 p-5 sm:flex-row sm:flex-wrap sm:items-center">
                @if ($isFinished)
                    <a href="{{ route('competitions.show', $registration->competition) }}" class="siperlo-btn-secondary px-4 py-2 text-sm">Detail Lomba</a>
                    @if ($registration->result_status)
                        <a href="{{ route('registrations.results.show', $registration) }}" class="text-sm font-semibold text-campus-green hover:underline">Lihat laporan hasil</a>
                    @endif
                    <a href="{{ route('sop.index') }}" class="text-sm font-semibold text-muted-ink hover:text-campus-green sm:ml-auto">Lihat SOP</a>
                @else
                    @if ($registration->canReportResult())
                        <a href="{{ route('registrations.results.create', $registration) }}" class="siperlo-btn-primary px-4 py-2 text-sm">
                            {{ $registration->result_status ? 'Perbarui Laporan Hasil' : 'Laporkan Hasil' }}
                        </a>
                    @endif
                    <a href="{{ route('competitions.show', $registration->competition) }}" class="{{ $registration->canReportResult() ? 'siperlo-btn-secondary' : 'siperlo-btn-primary' }} px-4 py-2 text-sm">Detail Lomba</a>
                    @if (! $registration->canReportResult() && $registration->result_status)
                        <a href="{{ route('registrations.results.show', $registration) }}" class="siperlo-btn-secondary px-4 py-2 text-sm">Lihat Laporan Hasil</a>
                    @endif
                    @if ($showMentorAction)
                        <a href="{{ route('mentors.index') }}" class="siperlo-btn-secondary px-4 py-2 text-sm">Cari Mentor</a>
                    @endif
                    @if ($showFundAction)
                        <a href="{{ route('fund-requests.create', ['registration_id' => $registration->id]) }}" class="siperlo-btn-secondary px-4 py-2 text-sm">Ajukan Bantuan Dana</a>
                    @endif
                    <a href="{{ route('sop.index') }}" class="text-sm font-semibold text-muted-ink hover:text-campus-green sm:ml-auto">Lihat SOP</a>
                @endif
            </div>

            @if ($registration->notes)
                <div class="p-5 text-sm text-ink/80">
                    <span class="font-semibold text-ink">Catatan dari Admin:</span> {{ $registration->notes }}
                </div>
            @endif
        </article>
    @empty
        <div class="siperlo-surface rounded-md p-8 text-center">
            <img src="{{ asset('brand/siperlo-empty.svg') }}" alt="" aria-hidden="true" loading="lazy" decoding="async" class="mx-auto mb-4 h-32 w-auto">
            <h2 class="font-display text-xl font-bold">Belum ada lomba yang diikuti.</h2>
            <p class="mt-2 text-sm text-ink/80">Mulai dari daftar lomba, lalu pilih kompetisi yang sesuai minatmu.</p>
            <a href="{{ route('competitions.index') }}" class="siperlo-btn-primary mt-5 inline-flex px-4 py-2 text-sm">Cari Lomba</a>
        </div>
    @endforelse
</div>
@endsection
