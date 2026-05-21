@extends('layouts.siperlo')

@section('title', 'Review Pendaftaran - SIPERLO')
@section('eyebrow', 'Admin')
@section('page_title', 'Review Pendaftaran')

@section('content')
@php
    $statusStyles = [
        'registered' => 'siperlo-status siperlo-status-neutral',
        'ongoing' => 'siperlo-status siperlo-status-info',
        'finished' => 'siperlo-status siperlo-status-success',
    ];
    $resultStyles = [
        'pending' => 'siperlo-status siperlo-status-warning',
        'approved' => 'siperlo-status siperlo-status-success',
        'revision' => 'siperlo-status siperlo-status-warning',
        'rejected' => 'siperlo-status siperlo-status-danger',
    ];
@endphp

<section class="siperlo-surface rounded-md p-5">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <h2 class="font-display text-xl font-bold">Daftar Pendaftaran Lomba</h2>
            <p class="mt-1 text-sm text-ink/80">Kelola status utama, validasi hasil, dan catatan monitoring mahasiswa.</p>
        </div>

        <form method="GET" action="{{ route('admin.registrations.index') }}" class="grid gap-3 sm:grid-cols-[280px_auto]">
            <input type="hidden" name="status" value="{{ $selectedStatus }}">
            <div>
                <label for="registration-search" class="text-sm font-semibold text-ink">Cari pendaftaran</label>
                <input id="registration-search" name="search" value="{{ $search }}" placeholder="Mahasiswa, lomba, mentor, atau hasil"
                       class="siperlo-field mt-1 w-full">
            </div>
            <button class="siperlo-btn-primary self-end px-4 py-2 text-sm">Cari</button>
        </form>
    </div>

    <div class="mt-5 flex flex-wrap gap-2" aria-label="Filter status pendaftaran">
        @foreach ($statusTabs as $status => $label)
            @php
                $count = $status === 'all' ? $totalRegistrations : ($statusCounts[$status] ?? 0);
                $isActive = $selectedStatus === $status;
            @endphp
            <a href="{{ route('admin.registrations.index', array_filter(['status' => $status, 'search' => $search ?: null])) }}"
               @if ($isActive) aria-current="page" @endif
               class="siperlo-tab {{ $isActive ? 'siperlo-tab-active' : '' }}">
                {{ $label }}
                <span class="siperlo-tab-count">{{ $count }}</span>
            </a>
        @endforeach
    </div>
</section>

<div class="mt-5 grid gap-4">
    @forelse ($registrations as $registration)
        @php
            $hasResultReport = $registration->hasResultReport();
            $isResultReviewTab = $selectedStatus === 'result_pending';
            $isRevisionResult = $registration->result_status === 'revision';
            $hasFinalResult = $registration->hasFinalResult();
            $isOngoingResultFlow = $registration->primaryStatus() === 'ongoing';
            $isPrimaryStatusLocked = $isResultReviewTab || $hasFinalResult || $isOngoingResultFlow;
            $statusClass = $statusStyles[$registration->primaryStatus()] ?? 'siperlo-status siperlo-status-neutral';
            $resultClass = $registration->result_status
                ? ($resultStyles[$registration->result_status] ?? 'siperlo-status siperlo-status-neutral')
                : 'siperlo-status siperlo-status-neutral';
            $fundStatus = $registration->latestFundRequest?->statusLabel() ?: 'Belum diajukan';
        @endphp
        <article class="siperlo-surface rounded-md p-5">
            <div class="grid gap-5 xl:grid-cols-[1fr_360px]">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-muted-ink">{{ $registration->competition->category }} - {{ $registration->competition->type }}</div>
                            <h2 class="mt-1 font-display text-xl font-bold leading-snug">{{ $registration->competition->title }}</h2>
                            <div class="mt-2 text-sm text-ink/80">{{ $registration->user->name }}</div>
                        </div>
                        <span class="{{ $statusClass }}">{{ $registration->primaryStatusLabel() }}</span>
                    </div>

                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                        <div class="siperlo-data-cell">
                            <dt class="font-semibold text-ink">Mentor</dt>
                            <dd class="mt-1 text-ink/80">{{ $registration->mentor?->user?->name ?: 'Belum ditentukan' }}</dd>
                        </div>
                        <div class="siperlo-data-cell">
                            <dt class="font-semibold text-ink">Dana</dt>
                            <dd class="mt-1 text-ink/80">{{ $fundStatus }}</dd>
                        </div>
                        <div class="siperlo-data-cell">
                            <dt class="font-semibold text-ink">Didaftarkan</dt>
                            <dd class="mt-1 text-ink/80">{{ $registration->created_at->translatedFormat('d M Y H:i') }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 rounded-md border border-border-line bg-panel p-4 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="font-semibold text-ink">Laporan Hasil</div>
                            <span class="{{ $resultClass }}">{{ $registration->resultStatusLabel() }}</span>
                        </div>
                        @if (! $hasResultReport)
                            <div class="mt-2 text-ink/80">Belum ada laporan hasil dari mahasiswa.</div>
                        @endif
                        @if ($registration->result)
                            <div class="mt-2 text-ink/80">Capaian: <span class="font-semibold text-ink">{{ $registration->result }}</span></div>
                        @endif
                        @if ($registration->result_proof_file)
                            <a href="{{ Storage::url($registration->result_proof_file) }}" target="_blank" rel="noopener" class="siperlo-btn-secondary mt-3 px-3 py-2 text-sm">Lihat Bukti</a>
                        @endif
                        @if ($registration->result_admin_notes)
                            <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 p-3 text-amber-900">
                                <span class="font-semibold">Catatan review hasil:</span> {{ $registration->result_admin_notes }}
                            </div>
                        @endif
                    </div>

                    @if ($registration->notes)
                        <div class="mt-3 rounded-md border border-border-line bg-admin-note-surface p-3 text-sm text-ink/80">
                            <span class="font-semibold text-ink">Catatan admin:</span> {{ $registration->notes }}
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.registrations.update', $registration) }}" class="rounded-md border border-border-line bg-admin-note-surface p-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="registration-status-{{ $registration->id }}" class="text-sm font-semibold text-ink">Status utama</label>
                        @if ($isPrimaryStatusLocked)
                            <input type="hidden" name="status" value="{{ $registration->primaryStatus() }}">
                            <div id="registration-status-{{ $registration->id }}" class="mt-1 rounded-md border border-border-line bg-panel px-3 py-2 text-sm font-semibold text-ink">
                                {{ $registration->primaryStatusLabel() }}
                                <span class="ml-2 text-xs font-normal text-muted-ink">(dikunci)</span>
                            </div>
                        @else
                            <select id="registration-status-{{ $registration->id }}" name="status" class="siperlo-field mt-1 w-full">
                                @foreach (\App\Models\Registration::PRIMARY_STATUSES as $status => $label)
                                    @continue($status === 'finished' && ! $hasResultReport)
                                    <option value="{{ $status }}" @selected($registration->primaryStatus() === $status)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    @if ($hasResultReport && $isResultReviewTab)
                        <div class="mt-3 rounded-md border border-border-line bg-panel p-3 text-sm text-ink/80">
                            Status utama akan otomatis mengikuti keputusan validasi laporan hasil.
                        </div>

                        <div class="mt-3">
                            <label for="registration-result-{{ $registration->id }}" class="text-sm font-semibold text-ink">Hasil lomba</label>
                            <input id="registration-result-{{ $registration->id }}" name="result" value="{{ $registration->result }}" placeholder="Contoh: Finalis Nasional" class="siperlo-field mt-1 w-full">
                        </div>

                        <div class="mt-3">
                            <label for="registration-result-status-{{ $registration->id }}" class="text-sm font-semibold text-ink">Status laporan hasil</label>
                            <select id="registration-result-status-{{ $registration->id }}" name="result_status" class="siperlo-field mt-1 w-full">
                                @foreach (\App\Models\Registration::RESULT_STATUSES as $status => $label)
                                    <option value="{{ $status }}" @selected($registration->result_status === $status)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-3">
                            <label for="registration-result-notes-{{ $registration->id }}" class="text-sm font-semibold text-ink">Catatan review hasil</label>
                            <textarea id="registration-result-notes-{{ $registration->id }}" name="result_admin_notes" rows="3" placeholder="Catatan untuk mahasiswa" class="siperlo-field mt-1 w-full">{{ $registration->result_admin_notes }}</textarea>
                        </div>
                    @else
                        <div class="mt-3 rounded-md border border-border-line bg-panel p-3 text-sm text-ink/80">
                            @if ($isRevisionResult)
                                Laporan perlu revisi. Mahasiswa harus memperbarui laporan hasil sebelum proses bisa difinalkan.
                            @elseif ($isOngoingResultFlow && ! $hasResultReport)
                                Menunggu mahasiswa mengirim laporan hasil. Status utama dikunci setelah lomba berjalan.
                            @elseif ($registration->result_status === 'pending')
                                Review laporan lewat tab <span class="font-semibold">Validasi Hasil</span>.
                            @elseif (! $hasResultReport)
                                Belum ada laporan hasil yang bisa direview.
                            @else
                                Kamu masih bisa memperbarui catatan monitoring di bawah.
                            @endif
                        </div>
                    @endif

                    <div class="mt-3">
                        <label for="registration-notes-{{ $registration->id }}" class="text-sm font-semibold text-ink">Catatan monitoring</label>
                        <textarea id="registration-notes-{{ $registration->id }}" name="notes" rows="3" placeholder="Catatan internal pendaftaran" class="siperlo-field mt-1 w-full">{{ $registration->notes }}</textarea>
                    </div>

                    <x-submit-button label="Simpan Review" pending-label="Menyimpan..." class="mt-4 w-full" />
                </form>
            </div>
        </article>
    @empty
        <div class="siperlo-empty">Tidak ada pendaftaran untuk filter ini.</div>
    @endforelse
</div>

<div class="mt-5">{{ $registrations->links() }}</div>
@endsection
