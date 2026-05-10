@extends('layouts.siperlo')

@section('title', 'Laporkan Hasil - SIPERLO')
@section('page_title', 'Laporkan Hasil')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Lomba Saya', 'route' => 'registrations.index'],
        ['label' => $registration->competition->title, 'url' => route('competitions.show', $registration->competition)],
        ['label' => 'Laporkan Hasil'],
    ]" />
@endsection

@section('content')
@php
    $isReadOnly = $readOnly ?? false;
    $statusClass = match ($registration->result_status) {
        'approved' => 'siperlo-status siperlo-status-success',
        'pending' => 'siperlo-status siperlo-status-info',
        'revision' => 'siperlo-status siperlo-status-warning',
        'rejected' => 'siperlo-status siperlo-status-danger',
        default => 'siperlo-status siperlo-status-neutral',
    };
@endphp

<div class="grid gap-6 xl:grid-cols-[1fr_360px]">
    <form method="POST" action="{{ route('registrations.results.store', $registration) }}" enctype="multipart/form-data" class="siperlo-surface rounded-md p-6">
        @csrf
        <div class="rounded-md border border-border-line bg-admin-note-surface p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-muted-ink">{{ $registration->competition->category }} - {{ $registration->competition->type }}</div>
                    <h2 class="mt-1 font-display text-2xl font-bold">{{ $registration->competition->title }}</h2>
                    <div class="mt-2 text-sm text-ink/80">{{ $isReadOnly ? 'Ringkasan laporan hasil yang sudah dikirim.' : 'Isi capaian dan unggah bukti setelah lomba selesai.' }}</div>
                </div>
                <span class="{{ $statusClass }}">{{ $registration->resultStatusLabel() }}</span>
            </div>
        </div>

        <div class="mt-5 grid gap-4">
            <div>
                <label for="result" class="text-sm font-semibold">Hasil/Capaian <span class="siperlo-required">*</span></label>
                <input name="result" value="{{ old('result', $registration->result) }}" placeholder="Contoh: Juara 2, Finalis, Peserta, Tidak lolos babak final"
                       id="result" class="siperlo-field mt-1 w-full" @readonly($isReadOnly) required
                       aria-invalid="@error('result') true @else false @enderror"
                       aria-describedby="result-help @error('result') result-error @enderror">
                <p id="result-help" class="mt-1 text-xs text-muted-ink">
                    {{ $isReadOnly ? 'Capaian ini menjadi arsip laporan hasil.' : 'Gunakan capaian yang resmi dan mudah diverifikasi admin.' }}
                </p>
                @error('result')
                    <div id="result-error" class="siperlo-error">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="result_description" class="text-sm font-semibold">Ringkasan Laporan</label>
                <textarea id="result_description" name="result_description" rows="6" class="siperlo-field mt-1 w-full" placeholder="Ceritakan tahap lomba, hasil akhir, dan informasi penting lain." @readonly($isReadOnly)
                          aria-invalid="@error('result_description') true @else false @enderror"
                          @error('result_description') aria-describedby="result_description-error" @enderror>{{ old('result_description', $registration->result_description) }}</textarea>
                @error('result_description')
                    <div id="result_description-error" class="siperlo-error">{{ $message }}</div>
                @enderror
            </div>
            @if ($isReadOnly)
                <div>
                    <div class="text-sm font-semibold">Bukti Hasil</div>
                    @if ($registration->result_proof_file)
                        <a href="{{ Storage::url($registration->result_proof_file) }}" target="_blank" rel="noopener" class="siperlo-btn-secondary mt-2 px-4 py-2 text-sm">Lihat Bukti</a>
                    @else
                        <div class="mt-2 text-sm text-muted-ink">Tidak ada bukti yang diunggah.</div>
                    @endif
                </div>
            @else
                <div>
                    <label for="result_proof_file" class="text-sm font-semibold">Bukti Hasil</label>
                    <input id="result_proof_file" type="file" name="result_proof_file" class="siperlo-file mt-1 w-full"
                           aria-describedby="result_proof_file-help @error('result_proof_file') result_proof_file-error @enderror"
                           aria-invalid="@error('result_proof_file') true @else false @enderror">
                    <p id="result_proof_file-help" class="mt-1 text-xs text-muted-ink">PDF, DOC, DOCX, JPG, JPEG, atau PNG. Maksimal 4 MB.</p>
                    @error('result_proof_file')
                        <div id="result_proof_file-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                    @if ($registration->result_proof_file)
                        <div class="mt-2 text-xs text-muted-ink">Bukti sebelumnya akan tetap dipakai jika tidak mengunggah file baru.</div>
                    @endif
                </div>
            @endif
        </div>

        @if ($registration->result_admin_notes)
            <div class="mt-5 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                <span class="font-semibold">Catatan Review Hasil:</span> {{ $registration->result_admin_notes }}
            </div>
        @endif

        <div class="mt-6 flex flex-wrap gap-3">
            @unless ($isReadOnly)
                <x-submit-button label="Kirim Laporan" pending-label="Mengirim laporan..." />
            @endunless
            <a href="{{ route('registrations.index') }}" class="siperlo-btn-secondary px-5 py-2 text-sm">Kembali</a>
        </div>
    </form>

    <aside class="siperlo-surface rounded-md p-5">
        <h2 class="font-display text-lg font-bold">Alur Validasi</h2>
        <ol class="mt-4 space-y-3 text-sm text-ink/80">
            <li><span class="font-semibold text-ink">1. Kirim laporan.</span> Mahasiswa mengisi hasil dan bukti lomba.</li>
            <li><span class="font-semibold text-ink">2. Admin review.</span> Laporan dapat disetujui, diminta revisi, atau ditolak final.</li>
            <li><span class="font-semibold text-ink">3. Status selesai.</span> Jika disetujui atau ditolak final, lomba masuk riwayat.</li>
        </ol>
    </aside>
</div>
@endsection
