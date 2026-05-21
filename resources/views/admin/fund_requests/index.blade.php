@extends('layouts.siperlo')

@section('title', 'Review Dana - SIPERLO')
@section('eyebrow', 'Admin')
@section('page_title', 'Review Pengajuan Dana')

@section('content')
@php
    $statusStyles = [
        'pending' => 'siperlo-status siperlo-status-warning',
        'approved' => 'siperlo-status siperlo-status-success',
        'revision' => 'siperlo-status siperlo-status-warning',
        'rejected' => 'siperlo-status siperlo-status-danger',
    ];
@endphp

<section class="siperlo-surface rounded-md p-5">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <h2 class="font-display text-xl font-bold">Daftar Pengajuan Dana</h2>
            <p class="mt-1 text-sm text-ink/80">Pantau kebutuhan dana lomba, dokumen pendukung, dan keputusan review.</p>
        </div>

        <form method="GET" action="{{ route('admin.fund-requests.index') }}" class="grid gap-3 sm:grid-cols-[280px_auto]">
            <input type="hidden" name="status" value="{{ $selectedStatus }}">
            <div>
                <label for="fund-search" class="text-sm font-semibold text-ink">Cari pengajuan</label>
                <input id="fund-search" name="search" value="{{ $search }}" placeholder="Mahasiswa, lomba, atau tujuan dana" class="siperlo-field mt-1 w-full">
            </div>
            <button class="siperlo-btn-primary self-end px-4 py-2 text-sm">Cari</button>
        </form>
    </div>

    <div class="mt-5 flex flex-wrap gap-2" aria-label="Filter status pengajuan dana">
        @foreach ($statusTabs as $status => $label)
            @php
                $count = $status === 'all' ? $totalRequests : ($statusCounts[$status] ?? 0);
                $isActive = $selectedStatus === $status;
            @endphp
            <a href="{{ route('admin.fund-requests.index', array_filter(['status' => $status, 'search' => $search ?: null])) }}"
               @if ($isActive) aria-current="page" @endif
               class="siperlo-tab {{ $isActive ? 'siperlo-tab-active' : '' }}">
                {{ $label }}
                <span class="siperlo-tab-count">{{ $count }}</span>
            </a>
        @endforeach
    </div>
</section>

<div class="mt-5 grid gap-4">
    @forelse ($requests as $request)
        @php
            $badgeClass = $statusStyles[$request->status] ?? 'siperlo-status siperlo-status-neutral';
            $isPending = $request->status === 'pending';
        @endphp
        <article class="siperlo-surface rounded-md p-5">
            <div class="grid gap-5 xl:grid-cols-[1fr_360px]">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-muted-ink">{{ $request->registration->competition->title }}</div>
                            <h2 class="mt-1 font-display text-xl font-bold leading-snug">
                                {{ $request->user->name }} - Rp {{ number_format($request->amount, 0, ',', '.') }}
                            </h2>
                        </div>
                        <span class="{{ $badgeClass }}">{{ $request->statusLabel() }}</span>
                    </div>

                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                        <div class="siperlo-data-cell">
                            <dt class="font-semibold text-ink">Mahasiswa</dt>
                            <dd class="mt-1 text-ink/80">{{ $request->user->name }}</dd>
                        </div>
                        <div class="siperlo-data-cell">
                            <dt class="font-semibold text-ink">Nominal</dt>
                            <dd class="mt-1 font-semibold text-ink">Rp {{ number_format($request->amount, 0, ',', '.') }}</dd>
                        </div>
                        <div class="siperlo-data-cell">
                            <dt class="font-semibold text-ink">Diajukan</dt>
                            <dd class="mt-1 text-ink/80">{{ $request->created_at->translatedFormat('d M Y H:i') }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 rounded-md border border-border-line bg-panel p-4 text-sm leading-6 text-ink/80">
                        <div class="font-semibold text-ink">Tujuan dana</div>
                        <div class="mt-1 font-semibold text-campus-green">{{ $request->purpose }}</div>
                        @if ($request->description)
                            <p class="mt-2">{{ $request->description }}</p>
                        @endif
                    </div>

                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        @if ($request->proposal_file)
                            <a href="{{ Storage::url($request->proposal_file) }}" target="_blank" rel="noopener" class="siperlo-btn-secondary px-3 py-2 text-sm">Lihat Proposal</a>
                        @endif
                        @if ($request->supporting_docs)
                            <a href="{{ Storage::url($request->supporting_docs) }}" target="_blank" rel="noopener" class="siperlo-btn-secondary px-3 py-2 text-sm">Lihat Dokumen</a>
                        @endif
                    </div>

                    @if ($request->admin_notes)
                        <div class="mt-3 rounded-md border border-border-line bg-admin-note-surface p-3 text-sm text-ink/80">
                            <span class="font-semibold text-ink">Catatan admin:</span> {{ $request->admin_notes }}
                        </div>
                    @endif
                </div>

                @if ($isPending)
                    <form method="POST" action="{{ route('admin.fund-requests.update', $request) }}" class="rounded-md border border-border-line bg-admin-note-surface p-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="fund-status-{{ $request->id }}" class="text-sm font-semibold text-ink">Status review</label>
                            <select id="fund-status-{{ $request->id }}" name="status" class="siperlo-field mt-1 w-full">
                                @foreach (\App\Models\FundRequest::REVIEW_STATUSES as $status => $label)
                                    <option value="{{ $status }}" @selected($request->status === $status)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-3">
                            <label for="fund-notes-{{ $request->id }}" class="text-sm font-semibold text-ink">Catatan admin</label>
                            <textarea id="fund-notes-{{ $request->id }}" name="admin_notes" rows="4" placeholder="Tulis alasan keputusan" class="siperlo-field mt-1 w-full">{{ $request->admin_notes }}</textarea>
                        </div>

                        <x-submit-button label="Simpan Review" pending-label="Menyimpan..." class="mt-4 w-full" />
                    </form>
                @else
                    <aside class="rounded-md border border-border-line bg-admin-note-surface p-4">
                        <div class="text-sm font-semibold text-ink">Status review</div>
                        <div class="mt-1 rounded-md border border-border-line bg-panel px-3 py-2 text-sm font-semibold text-ink">
                            {{ $request->statusLabel() }}
                            <span class="ml-2 text-xs font-normal text-muted-ink">(final)</span>
                        </div>
                        <p class="mt-2 text-sm text-ink/80">Keputusan review sudah final.</p>
                        @if ($request->admin_notes)
                            <div class="mt-3 text-sm">
                                <div class="font-semibold text-ink">Catatan admin</div>
                                <div class="mt-1 rounded-md border border-border-line bg-panel p-3 text-ink/80">{{ $request->admin_notes }}</div>
                            </div>
                        @endif
                    </aside>
                @endif
            </div>
        </article>
    @empty
        <div class="siperlo-empty">Tidak ada pengajuan dana untuk filter ini.</div>
    @endforelse
</div>

<div class="mt-5">{{ $requests->links() }}</div>
@endsection
