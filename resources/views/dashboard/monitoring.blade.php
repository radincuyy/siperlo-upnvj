@extends('layouts.siperlo')

@section('title', $title.' - SIPERLO')
@section('eyebrow', $readOnly ? 'Monitoring Read-Only' : 'Administrasi')
@section('page_title', $title)

@section('content')
@php
    $actionableCount = ($stats['mentorPending'] ?? 0) + ($stats['fundPending'] ?? 0);
@endphp

<div class="grid gap-4 lg:grid-cols-[1.2fr_1fr]">
    {{-- Perlu tindakan: disorot --}}
    <div class="siperlo-stat rounded-md p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-xs font-semibold uppercase text-muted-ink">Perlu Tindakan</div>
                <div class="mt-2 font-display text-4xl font-bold {{ $actionableCount > 0 ? 'text-amber-800' : 'text-campus-green' }}">{{ $actionableCount }}</div>
                <div class="mt-1 text-sm text-ink/80">Pengajuan yang menunggu review admin.</div>
            </div>
            <span class="siperlo-status {{ $actionableCount > 0 ? 'siperlo-status-warning' : 'siperlo-status-neutral' }}">
                {{ $actionableCount > 0 ? 'Menunggu' : 'Kosong' }}
            </span>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @if (! $readOnly)
                <a href="{{ route('admin.mentor-requests.index', ['status' => 'pending']) }}" class="flex items-center justify-between rounded-md border border-border-line bg-panel px-3 py-2 text-sm transition hover:border-campus-green/40 hover:bg-hover-green-surface">
                    <span class="font-semibold text-ink">Mentor</span>
                    <span class="font-display text-xl font-bold text-campus-green">{{ $stats['mentorPending'] ?? 0 }}</span>
                </a>
                <a href="{{ route('admin.fund-requests.index', ['status' => 'pending']) }}" class="flex items-center justify-between rounded-md border border-border-line bg-panel px-3 py-2 text-sm transition hover:border-campus-green/40 hover:bg-hover-green-surface">
                    <span class="font-semibold text-ink">Dana</span>
                    <span class="font-display text-xl font-bold text-campus-green">{{ $stats['fundPending'] ?? 0 }}</span>
                </a>
            @else
                <div class="flex items-center justify-between rounded-md border border-border-line bg-panel px-3 py-2 text-sm">
                    <span class="font-semibold text-ink">Mentor</span>
                    <span class="font-display text-xl font-bold text-campus-green">{{ $stats['mentorPending'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between rounded-md border border-border-line bg-panel px-3 py-2 text-sm">
                    <span class="font-semibold text-ink">Dana</span>
                    <span class="font-display text-xl font-bold text-campus-green">{{ $stats['fundPending'] ?? 0 }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Info tambahan: lebih ringan --}}
    <dl class="siperlo-stagger grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-md border border-border-line bg-panel p-4">
            <dt class="text-xs font-semibold uppercase text-muted-ink">Total Lomba</dt>
            <dd class="mt-1 font-display text-2xl font-bold text-ink">{{ $stats['competitions'] }}</dd>
        </div>
        <div class="rounded-md border border-border-line bg-panel p-4">
            <dt class="text-xs font-semibold uppercase text-muted-ink">Pendaftar</dt>
            <dd class="mt-1 font-display text-2xl font-bold text-ink">{{ $stats['registrations'] }}</dd>
        </div>
        <div class="rounded-md border border-border-line bg-panel p-4">
            <dt class="text-xs font-semibold uppercase text-muted-ink">Selesai</dt>
            <dd class="mt-1 font-display text-2xl font-bold text-ink">{{ $stats['finished'] }}</dd>
        </div>
        <div class="rounded-md border border-border-line bg-panel p-4">
            <dt class="text-xs font-semibold uppercase text-muted-ink">Mentor Aktif</dt>
            <dd class="mt-1 font-display text-2xl font-bold text-ink">{{ $stats['mentors'] }}</dd>
        </div>
    </dl>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_360px]">
    <section class="siperlo-surface rounded-md p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-display text-lg font-bold">Pendaftaran Terbaru</h2>
                <p class="mt-1 text-sm text-ink/80">Ringkasan aktivitas terbaru yang perlu dipantau admin.</p>
            </div>
            <span class="siperlo-status siperlo-status-neutral">{{ $recentRegistrations->count() }} data</span>
        </div>

        {{-- Desktop table --}}
        <div class="mt-4 hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-border-line text-sm">
                <thead class="bg-admin-note-surface text-left text-xs font-semibold uppercase text-muted-ink">
                    <tr>
                        <th class="px-4 py-3">Mahasiswa</th>
                        <th class="px-4 py-3">Lomba</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Mentor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-line">
                    @forelse ($recentRegistrations as $registration)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-ink">{{ $registration->user->name }}</td>
                            <td class="px-4 py-3 text-ink/80">{{ $registration->competition->title }}</td>
                            <td class="px-4 py-3">
                                <span class="siperlo-status {{ $registration->primaryStatus() === 'finished' ? 'siperlo-status-success' : ($registration->primaryStatus() === 'ongoing' ? 'siperlo-status-info' : 'siperlo-status-neutral') }}">
                                    {{ $registration->primaryStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-ink/80">{{ $registration->mentor?->user?->name ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-muted-ink">Belum ada pendaftaran terbaru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile list --}}
        <div class="mt-4 space-y-3 md:hidden">
            @forelse ($recentRegistrations as $registration)
                <div class="rounded-md border border-border-line bg-admin-note-surface p-3 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-semibold text-ink">{{ $registration->user->name }}</div>
                            <div class="mt-1 text-ink/80">{{ $registration->competition->title }}</div>
                        </div>
                        <span class="siperlo-status {{ $registration->primaryStatus() === 'finished' ? 'siperlo-status-success' : ($registration->primaryStatus() === 'ongoing' ? 'siperlo-status-info' : 'siperlo-status-neutral') }} shrink-0">
                            {{ $registration->primaryStatusLabel() }}
                        </span>
                    </div>
                    <div class="mt-2 text-xs text-muted-ink">Mentor: {{ $registration->mentor?->user?->name ?: '-' }}</div>
                </div>
            @empty
                <div class="rounded-md border border-border-line bg-admin-note-surface p-4 text-center text-sm text-muted-ink">Belum ada pendaftaran terbaru.</div>
            @endforelse
        </div>
    </section>

    <aside class="space-y-5">
        <div class="siperlo-surface rounded-md p-5">
            <h2 class="font-display text-lg font-bold">Sebaran Status</h2>
            <div class="mt-4 space-y-4">
                @foreach (\App\Models\Registration::PRIMARY_STATUSES as $status => $label)
                    @php
                        $total = $registrationsByStatus[$status] ?? 0;
                        $totalRegs = $stats['registrations'] ?: 1;
                        $percent = (int) round(($total / $totalRegs) * 100);
                        $width = min(100, $percent);
                    @endphp
                    <div>
                        <div class="flex justify-between gap-3 text-sm">
                            <span class="font-semibold text-ink">{{ $label }}</span>
                            <span class="text-ink/80">{{ $total }} <span class="text-muted-ink">({{ $percent }}%)</span></span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-border-line" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100" aria-label="Sebaran status {{ $label }}: {{ $total }} dari {{ $stats['registrations'] }} pendaftaran">
                            <div class="h-2 rounded-full bg-campus-green" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="siperlo-surface rounded-md p-5">
            <h2 class="font-display text-lg font-bold">Dana Terbaru</h2>
            <div class="mt-4 space-y-3">
                @forelse ($recentFunds as $fund)
                    <div class="rounded-md border border-border-line bg-admin-note-surface p-3 text-sm">
                        <div class="font-semibold text-ink">{{ $fund->registration->competition->title }}</div>
                        <div class="mt-1 text-ink/80">Rp {{ number_format($fund->amount, 0, ',', '.') }}</div>
                        <span class="siperlo-status {{ $fund->status === 'approved' ? 'siperlo-status-success' : ($fund->status === 'rejected' ? 'siperlo-status-danger' : 'siperlo-status-warning') }} mt-2">
                            {{ method_exists($fund, 'statusLabel') ? $fund->statusLabel() : ucfirst($fund->status) }}
                        </span>
                    </div>
                @empty
                    <div class="rounded-md border border-border-line bg-admin-note-surface p-3 text-sm text-muted-ink">Belum ada pengajuan dana.</div>
                @endforelse
            </div>
        </div>
    </aside>
</div>

<section class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="siperlo-surface rounded-md p-5 xl:col-span-2">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-display text-lg font-bold">Tren Pendaftaran 6 Bulan</h2>
                <p class="mt-1 text-sm text-ink/80">Jumlah pendaftaran lomba per bulan, termasuk bulan berjalan.</p>
            </div>
            <span class="siperlo-status siperlo-status-neutral">{{ array_sum($chartTrend['values']) }} total</span>
        </div>
        <div class="mt-4 h-64">
            @if (empty($chartTrend['values']) || array_sum($chartTrend['values']) === 0)
                <div class="flex h-full items-center justify-center text-sm text-muted-ink">Belum ada data pendaftaran dalam 6 bulan terakhir.</div>
            @else
                <canvas id="siperlo-chart-trend" aria-label="Grafik tren pendaftaran 6 bulan terakhir" role="img"></canvas>
                <div class="sr-only">
                    <table>
                        <caption>Data tren pendaftaran 6 bulan</caption>
                        <thead><tr><th>Bulan</th><th>Pendaftaran</th></tr></thead>
                        <tbody>
                            @foreach ($chartTrend['labels'] as $i => $label)
                                <tr><td>{{ $label }}</td><td>{{ $chartTrend['values'][$i] ?? 0 }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="siperlo-surface rounded-md p-5">
        <h2 class="font-display text-lg font-bold">Pendaftaran per Kategori</h2>
        <p class="mt-1 text-sm text-ink/80">Kategori lomba yang paling banyak diikuti.</p>
        <div class="mt-4 h-64">
            @if (empty($chartByCategory['labels']))
                <div class="flex h-full items-center justify-center text-sm text-muted-ink">Belum ada data pendaftaran.</div>
            @else
                <canvas id="siperlo-chart-category" aria-label="Grafik pendaftaran per kategori lomba" role="img"></canvas>
                <div class="sr-only">
                    <table>
                        <caption>Data pendaftaran per kategori</caption>
                        <thead><tr><th>Kategori</th><th>Pendaftaran</th></tr></thead>
                        <tbody>
                            @foreach ($chartByCategory['labels'] as $i => $label)
                                <tr><td>{{ $label }}</td><td>{{ $chartByCategory['values'][$i] ?? 0 }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="siperlo-surface rounded-md p-5 xl:col-span-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-display text-lg font-bold">Sebaran Hasil Lomba</h2>
                <p class="mt-1 text-sm text-ink/80">Distribusi laporan hasil berdasarkan keputusan validasi admin.</p>
            </div>
            <span class="siperlo-status siperlo-status-neutral">{{ array_sum($chartResults['values']) }} pendaftaran</span>
        </div>
        <div class="mt-4 h-56">
            @if (empty($chartResults['values']) || array_sum($chartResults['values']) === 0)
                <div class="flex h-full items-center justify-center text-sm text-muted-ink">Belum ada data hasil lomba.</div>
            @else
                <canvas id="siperlo-chart-results" aria-label="Grafik sebaran hasil lomba" role="img"></canvas>
                <div class="sr-only">
                    <table>
                        <caption>Data sebaran hasil lomba</caption>
                        <thead><tr><th>Status</th><th>Jumlah</th></tr></thead>
                        <tbody>
                            @foreach ($chartResults['labels'] as $i => $label)
                                <tr><td>{{ $label }}</td><td>{{ $chartResults['values'][$i] ?? 0 }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>
<script defer>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') return;

        const style = getComputedStyle(document.documentElement);
        const campusGreen = style.getPropertyValue('--siperlo-chart-green').trim() || '#155b32';
        const campusGreenSoft = style.getPropertyValue('--siperlo-chart-green-soft').trim() || 'rgba(21, 91, 50, 0.18)';
        const campusGold = style.getPropertyValue('--siperlo-gold').trim() || '#d7a82f';
        const ink = style.getPropertyValue('--siperlo-ink').trim() || '#17201b';
        const mutedInk = style.getPropertyValue('--siperlo-muted').trim() || '#5f6b60';
        const borderLine = style.getPropertyValue('--siperlo-line').trim() || '#dbe2d9';
        const chartEmerald = style.getPropertyValue('--siperlo-chart-emerald').trim() || '#22c55e';
        const chartRed = style.getPropertyValue('--siperlo-chart-red').trim() || '#dc2626';
        const chartSlate = style.getPropertyValue('--siperlo-chart-slate').trim() || '#94a3b8';

        Chart.defaults.font.family = "'Atkinson Hyperlegible', ui-sans-serif, system-ui, sans-serif";
        Chart.defaults.color = mutedInk;
        Chart.defaults.borderColor = borderLine;

        const baseScales = {
            x: { grid: { display: false }, ticks: { color: mutedInk } },
            y: {
                beginAtZero: true,
                grid: { color: borderLine, drawBorder: false },
                ticks: { color: mutedInk, precision: 0 },
            },
        };

        const trendEl = document.getElementById('siperlo-chart-trend');
        if (trendEl) {
            new Chart(trendEl, {
                type: 'line',
                data: {
                    labels: @json($chartTrend['labels']),
                    datasets: [{
                        label: 'Pendaftaran',
                        data: @json($chartTrend['values']),
                        borderColor: campusGreen,
                        backgroundColor: campusGreenSoft,
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointBackgroundColor: campusGreen,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: baseScales,
                },
            });
        }

        const categoryEl = document.getElementById('siperlo-chart-category');
        if (categoryEl) {
            new Chart(categoryEl, {
                type: 'bar',
                data: {
                    labels: @json($chartByCategory['labels']),
                    datasets: [{
                        label: 'Pendaftaran',
                        data: @json($chartByCategory['values']),
                        backgroundColor: campusGreen,
                        borderRadius: 4,
                        maxBarThickness: 28,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, grid: { color: borderLine }, ticks: { color: mutedInk, precision: 0 } },
                        y: { grid: { display: false }, ticks: { color: ink } },
                    },
                },
            });
        }

        const resultsEl = document.getElementById('siperlo-chart-results');
        if (resultsEl) {
            new Chart(resultsEl, {
                type: 'bar',
                data: {
                    labels: @json($chartResults['labels']),
                    datasets: [{
                        label: 'Pendaftaran',
                        data: @json($chartResults['values']),
                        backgroundColor: [campusGreen, chartEmerald, campusGold, chartRed, chartSlate],
                        borderRadius: 4,
                        maxBarThickness: 56,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: baseScales,
                },
            });
        }
    });
</script>
@endpush
@endsection
