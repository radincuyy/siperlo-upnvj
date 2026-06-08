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
            $statusClass = $statusStyles[$registration->primaryStatus()] ?? 'siperlo-status siperlo-status-neutral';
            $resultClass = $registration->result_status
                ? ($resultStyles[$registration->result_status] ?? 'siperlo-status siperlo-status-neutral')
                : 'siperlo-status siperlo-status-neutral';
            $fundStatus = $registration->latestFundRequest?->statusLabel() ?: 'Belum diajukan';
            $proofStatus = $registration->proof_status;
            $proofClass = match ($proofStatus) {
                'verified' => 'siperlo-status siperlo-status-success',
                'pending' => 'siperlo-status siperlo-status-warning',
                'rejected' => 'siperlo-status siperlo-status-danger',
                default => 'siperlo-status siperlo-status-neutral',
            };
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

                    {{-- Bukti Pendaftaran --}}
                    <div class="mt-4 rounded-md border border-border-line bg-panel p-4 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="font-semibold text-ink">Bukti Pendaftaran Eksternal</div>
                            <span class="{{ $proofClass }}">{{ $registration->proofStatusLabel() }}</span>
                        </div>
                        @if ($registration->registration_proof_file)
                            <a href="{{ Storage::url($registration->registration_proof_file) }}" target="_blank" rel="noopener" class="siperlo-btn-secondary mt-3 inline-flex items-center gap-2 px-3 py-2 text-sm">
                                <x-lucide-file-check class="h-4 w-4" aria-hidden="true" />
                                Lihat Bukti Pendaftaran
                            </a>
                        @else
                            <div class="mt-2 text-ink/80">Mahasiswa belum mengupload bukti pendaftaran.</div>
                        @endif
                        @if ($registration->proof_admin_notes)
                            <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 p-3 text-amber-900">
                                <span class="font-semibold">Catatan verifikasi:</span> {{ $registration->proof_admin_notes }}
                            </div>
                        @endif
                    </div>

                    @if ($registration->notes)
                        <div class="mt-3 rounded-md border border-border-line bg-admin-note-surface p-3 text-sm text-ink/80">
                            <span class="font-semibold text-ink">Catatan admin:</span> {{ $registration->notes }}
                        </div>
                    @endif
                </div>

                @php
                    $canReviewProof = $registration->registration_proof_file && $registration->primaryStatus() === 'registered';
                    $canEditMonitoringNotes = $registration->primaryStatus() !== 'finished';
                    $canReviewResult = $hasResultReport && $isResultReviewTab;
                    $canShowReviewForm = $canReviewProof || $canEditMonitoringNotes || $canReviewResult;
                @endphp

                @if ($canShowReviewForm)
                    <form method="POST" action="{{ route('admin.registrations.update', $registration) }}" class="rounded-md border border-border-line bg-admin-note-surface p-4">
                        @csrf
                        @method('PATCH')

                        {{-- Proof verification (only when still registered) --}}
                        @if ($canReviewProof)
                            <div class="mt-3">
                                <label for="proof-status-{{ $registration->id }}" class="text-sm font-semibold text-ink">Bukti pendaftaran</label>
                                <select id="proof-status-{{ $registration->id }}" name="proof_status" class="siperlo-field mt-1 w-full">
                                    @foreach (\App\Models\Registration::PROOF_STATUSES as $status => $label)
                                        <option value="{{ $status }}" @selected($registration->proof_status === $status)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt-3">
                                <label for="proof-notes-{{ $registration->id }}" class="text-sm font-semibold text-ink">Catatan verifikasi bukti</label>
                                <textarea id="proof-notes-{{ $registration->id }}" name="proof_admin_notes" rows="3" placeholder="Wajib diisi jika bukti ditolak" class="siperlo-field mt-1 w-full">{{ $registration->proof_admin_notes }}</textarea>
                                <p class="mt-1 text-xs text-muted-ink">Catatan ini akan tampil ke mahasiswa jika bukti perlu diperbaiki.</p>
                            </div>
                            @if (! $registration->isProofVerified())
                                <div class="mt-2.5 flex items-start gap-1.5 rounded border border-amber-200 bg-amber-50/70 p-2.5 text-xs text-amber-800">
                                    <x-lucide-info class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" style="width: 16px; height: 16px;" aria-hidden="true" />
                                    <span>Verifikasi bukti pendaftaran akan otomatis mengubah status utama menjadi <strong>Berlangsung</strong>.</span>
                                </div>
                            @endif
                        @elseif ($registration->primaryStatus() === 'registered')
                            <div class="mt-3 flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                                <x-lucide-info class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" style="width: 16px; height: 16px;" aria-hidden="true" />
                                <span>Belum ada bukti pendaftaran. Mahasiswa perlu mengunggah bukti dari halaman detail lomba sebelum status dapat menjadi Berlangsung.</span>
                            </div>
                        @endif

                        @if ($canEditMonitoringNotes)
                            @if ($isRevisionResult)
                                <div class="mt-3 flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                                    <x-lucide-info class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" style="width: 16px; height: 16px;" aria-hidden="true" />
                                    <span>Laporan perlu revisi. Mahasiswa harus memperbarui laporan hasil sebelum proses bisa difinalkan.</span>
                                </div>
                            @endif

                            <div class="mt-3">
                                <label for="registration-notes-{{ $registration->id }}" class="text-sm font-semibold text-ink">Catatan monitoring</label>
                                <textarea id="registration-notes-{{ $registration->id }}" name="notes" rows="3" placeholder="Catatan internal pendaftaran" class="siperlo-field mt-1 w-full">{{ $registration->notes }}</textarea>
                            </div>
                        @endif

                        @if ($canReviewResult)
                            <div class="mt-3 flex items-start gap-2.5 rounded-md border border-campus-green/20 bg-soft-green/45 p-3 text-xs text-campus-green-deep">
                                <x-lucide-info class="mt-0.5 h-4 w-4 shrink-0 text-campus-green" style="width: 16px; height: 16px;" aria-hidden="true" />
                                <span>Status utama akan otomatis mengikuti keputusan validasi laporan hasil.</span>
                            </div>

                            <div class="mt-4">
                                <label for="registration-result-status-{{ $registration->id }}" class="text-sm font-semibold text-ink">Status laporan hasil</label>
                                <select id="registration-result-status-{{ $registration->id }}" name="result_status" class="siperlo-field mt-1.5 w-full">
                                    @foreach (\App\Models\Registration::RESULT_STATUSES as $status => $label)
                                        <option value="{{ $status }}" @selected($registration->result_status === $status)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-4">
                                <label for="registration-result-notes-{{ $registration->id }}" class="text-sm font-semibold text-ink">Catatan review hasil</label>
                                <textarea id="registration-result-notes-{{ $registration->id }}" name="result_admin_notes" rows="3" placeholder="Catatan untuk mahasiswa" class="siperlo-field mt-1.5 w-full">{{ $registration->result_admin_notes }}</textarea>
                            </div>
                        @endif

                        <x-submit-button label="Simpan Review" pending-label="Menyimpan..." class="mt-5 w-full" />
                    </form>
                @else
                    {{-- Status info card when no actions can be taken --}}
                    <div class="rounded-md border border-border-line bg-admin-note-surface p-4 text-sm">
                        @if ($registration->primaryStatus() === 'ongoing')
                            @if ($registration->result_status === 'pending')
                                <div class="flex items-start gap-2 text-amber-800">
                                    <x-lucide-info class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" style="width: 16px; height: 16px;" aria-hidden="true" />
                                    <div>
                                        <span class="font-semibold block text-amber-900">Laporan Hasil Menunggu Validasi</span>
                                        <span class="text-xs text-amber-700/90 block mt-1">Silakan lakukan peninjauan dan validasi laporan hasil melalui tab <strong class="underline">Validasi Hasil</strong>.</span>
                                    </div>
                                </div>
                            @elseif ($isRevisionResult)
                                <div class="flex items-start gap-2 text-amber-800">
                                    <x-lucide-info class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" style="width: 16px; height: 16px;" aria-hidden="true" />
                                    <div>
                                        <span class="font-semibold block text-amber-900">Menunggu Revisi Laporan</span>
                                        <span class="text-xs text-amber-700/90 block mt-1">Laporan perlu revisi. Mahasiswa harus memperbarui laporan hasil sebelum proses bisa difinalkan.</span>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-start gap-2 text-ink/80">
                                    <x-lucide-info class="mt-0.5 h-4 w-4 shrink-0 text-campus-green" style="width: 16px; height: 16px;" aria-hidden="true" />
                                    <div>
                                        <span class="font-semibold block text-ink">Perlombaan Sedang Berlangsung</span>
                                        <span class="text-xs text-muted-ink block mt-1">Mahasiswa sedang mengikuti perlombaan. Menunggu mahasiswa mengunggah laporan hasil.</span>
                                    </div>
                                </div>
                            @endif
                        @elseif ($registration->primaryStatus() === 'finished')
                            <div class="flex items-start gap-2 text-ink/80">
                                <x-lucide-check-circle class="mt-0.5 h-4 w-4 shrink-0 text-campus-green" style="width: 16px; height: 16px;" aria-hidden="true" />
                                <div>
                                    <span class="font-semibold block text-ink">Peninjauan Selesai</span>
                                    <span class="text-xs text-muted-ink block mt-1">Seluruh proses pendaftaran dan laporan hasil untuk perlombaan ini telah selesai ditinjau.</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="siperlo-empty">Tidak ada pendaftaran untuk filter ini.</div>
    @endforelse
</div>

<div class="mt-5">{{ $registrations->links() }}</div>
@endsection
