@extends('layouts.siperlo')

@section('title', 'SOP - SIPERLO')
@section('eyebrow', 'Panduan Proses')
@section('page_title', 'SOP Visual')

@section('content')
<div class="siperlo-surface mb-5 rounded-md p-5">
    <h2 class="font-display text-xl font-bold">Alur Utama Perlombaan</h2>
    <p class="mt-1 text-sm text-ink/80">Gunakan alur ini sebagai pegangan. Mentor dan dana adalah bantuan opsional, sedangkan laporan hasil menutup proses lomba.</p>
</div>

<div class="grid gap-5 lg:grid-cols-2 xl:grid-cols-4">
    @foreach ([
        ['title' => 'Pendaftaran Lomba', 'type' => 'utama',    'steps' => ['Pilih lomba', 'Baca detail dan SOP', 'Daftar internal', 'Masuk monitoring']],
        ['title' => 'Mentor',            'type' => 'opsional', 'steps' => ['Lihat profil mentor', 'Pilih sesuai bidang', 'Kirim alasan', 'Tunggu review']],
        ['title' => 'Bantuan Dana',      'type' => 'opsional', 'steps' => ['Pilih lomba aktif', 'Isi kebutuhan dana', 'Unggah proposal', 'Tunggu review']],
        ['title' => 'Pelaporan Hasil',   'type' => 'utama',    'steps' => ['Ikuti lomba', 'Kirim capaian', 'Admin validasi', 'Masuk riwayat selesai']],
    ] as $card)
        @php
            $isOpsional = $card['type'] === 'opsional';
            $badgeClass = $isOpsional ? 'siperlo-status-neutral' : 'siperlo-status-success';
            $badgeLabel = $isOpsional ? 'Opsional' : 'Utama';
        @endphp
        <section class="siperlo-surface rounded-md p-5">
            <div class="flex items-start justify-between gap-3">
                <h2 class="font-display text-lg font-bold">{{ $card['title'] }}</h2>
                <span class="siperlo-status {{ $badgeClass }}">{{ $badgeLabel }}</span>
            </div>
            <ol class="mt-5 space-y-3">
                @foreach ($card['steps'] as $step)
                    <li class="flex gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-campus-green text-sm font-bold text-white" aria-hidden="true">{{ $loop->iteration }}</div>
                        <div class="flex-1 rounded-md border border-border-line bg-admin-note-surface p-3 text-sm font-semibold text-ink">{{ $step }}</div>
                    </li>
                @endforeach
            </ol>
        </section>
    @endforeach
</div>
@endsection
