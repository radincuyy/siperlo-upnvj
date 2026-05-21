@extends('layouts.siperlo')

@section('title', 'Kelola Lomba - SIPERLO')
@section('eyebrow', 'Admin')
@section('page_title', 'Kelola Lomba')

@section('content')
@php
    $statusLabels = [
        'open' => 'Pendaftaran Buka',
        'soon' => 'Akan Datang',
        'closed' => 'Ditutup',
        'draft' => 'Draft',
    ];

    $statusClasses = [
        'open' => 'siperlo-status siperlo-status-success',
        'soon' => 'siperlo-status siperlo-status-neutral',
        'closed' => 'siperlo-status siperlo-status-warning',
        'draft' => 'siperlo-status siperlo-status-neutral',
    ];
@endphp

<section class="siperlo-surface rounded-md p-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <h2 class="font-display text-xl font-bold">Daftar Lomba</h2>
        <a href="{{ route('admin.competitions.create') }}" class="siperlo-btn-primary px-4 py-2 text-sm">Tambah Lomba</a>
    </div>
</section>

<section class="siperlo-surface mt-5 overflow-hidden rounded-md">
    {{-- Desktop table --}}
    <div class="hidden overflow-x-auto md:block">
        <table class="min-w-full divide-y divide-border-line text-sm">
            <thead class="bg-admin-note-surface text-left text-xs font-semibold uppercase text-muted-ink">
                <tr>
                    <th class="px-5 py-4">Lomba</th>
                    <th class="px-5 py-4">Kategori</th>
                    <th class="px-5 py-4">Deadline</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Pendaftar</th>
                    <th class="px-5 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-line">
                @forelse ($competitions as $competition)
                    <tr class="align-top">
                        <td class="px-5 py-4">
                            <div class="max-w-xl font-semibold text-ink">{{ $competition->title }}</div>
                            <div class="mt-1 text-muted-ink">{{ $competition->organizer }}</div>
                        </td>
                        <td class="px-5 py-4 text-ink/80">
                            <div class="font-semibold">{{ $competition->category }}</div>
                            <div class="mt-1 text-muted-ink">{{ $competition->type ?: 'Umum' }}</div>
                        </td>
                        <td class="px-5 py-4 text-ink/80">
                            {{ $competition->registration_deadline->translatedFormat('d M Y') }}
                            <div class="mt-1 text-xs text-muted-ink">{{ $competition->registration_deadline->translatedFormat('H:i') }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="{{ $statusClasses[$competition->displayStatus()] ?? 'siperlo-status siperlo-status-neutral' }}">
                                {{ $statusLabels[$competition->displayStatus()] ?? ucfirst($competition->displayStatus()) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 font-semibold text-ink">{{ $competition->registrations_count }}</td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap justify-end gap-2">
                                <a href="{{ route('admin.competitions.show', $competition) }}" class="siperlo-btn-secondary gap-1.5 px-3 py-2 text-sm">
                                    <x-lucide-eye class="h-4 w-4 shrink-0" aria-hidden="true" />
                                    Detail
                                </a>
                                <a href="{{ route('admin.competitions.edit', $competition) }}" class="siperlo-btn-secondary gap-1.5 px-3 py-2 text-sm">
                                    <x-lucide-pencil class="h-4 w-4 shrink-0" aria-hidden="true" />
                                    Edit
                                </a>
                                <div x-data="{ armed: false }" class="inline-flex items-center gap-2">
                                    <template x-if="!armed">
                                        <button type="button" @click="armed = true" class="siperlo-btn-secondary gap-1.5 px-3 py-2 text-sm">
                                            <x-lucide-trash-2 class="h-4 w-4 shrink-0" aria-hidden="true" />
                                            Hapus
                                        </button>
                                    </template>
                                    <template x-if="armed">
                                        <div class="inline-flex items-center gap-2">
                                            <span class="text-xs text-ink/80">Hapus lomba beserta {{ $competition->registrations_count }} pendaftaran?</span>
                                            <button type="button" @click="armed = false" class="siperlo-btn-secondary !px-3 py-2 text-sm">Batal</button>
                                            <form method="POST" action="{{ route('admin.competitions.destroy', $competition) }}">
                                                @csrf
                                                @method('DELETE')
                                                <x-submit-button variant="danger" label="Konfirmasi Hapus" pending-label="Menghapus..." class="!px-3" />
                                            </form>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10">
                            <div class="text-center text-sm text-muted-ink">
                                Belum ada lomba yang dibuat.
                                <a href="{{ route('admin.competitions.create') }}" class="font-semibold text-campus-green underline">Tambah lomba pertama</a>.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile list --}}
    <div class="divide-y divide-border-line md:hidden">
        @forelse ($competitions as $competition)
            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold text-ink">{{ $competition->title }}</div>
                        <div class="mt-1 text-sm text-muted-ink">{{ $competition->organizer }}</div>
                    </div>
                    <span class="{{ $statusClasses[$competition->displayStatus()] ?? 'siperlo-status siperlo-status-neutral' }} shrink-0">
                        {{ $statusLabels[$competition->displayStatus()] ?? ucfirst($competition->displayStatus()) }}
                    </span>
                </div>
                <dl class="mt-3 grid grid-cols-2 gap-2 text-xs text-ink/80">
                    <div><dt class="text-muted-ink">Kategori</dt><dd>{{ $competition->category }}</dd></div>
                    <div><dt class="text-muted-ink">Tipe</dt><dd>{{ $competition->type ?: 'Umum' }}</dd></div>
                    <div><dt class="text-muted-ink">Deadline</dt><dd>{{ $competition->registration_deadline->translatedFormat('d M Y H:i') }}</dd></div>
                    <div><dt class="text-muted-ink">Pendaftar</dt><dd>{{ $competition->registrations_count }}</dd></div>
                </dl>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('admin.competitions.show', $competition) }}" class="siperlo-btn-secondary gap-1.5 px-3 py-2 text-sm">
                        <x-lucide-eye class="h-4 w-4 shrink-0" aria-hidden="true" />
                        Detail
                    </a>
                    <a href="{{ route('admin.competitions.edit', $competition) }}" class="siperlo-btn-secondary gap-1.5 px-3 py-2 text-sm">
                        <x-lucide-pencil class="h-4 w-4 shrink-0" aria-hidden="true" />
                        Edit
                    </a>
                    <div x-data="{ armed: false }" class="inline-flex w-full flex-wrap items-center gap-2">
                        <template x-if="!armed">
                            <button type="button" @click="armed = true" class="siperlo-btn-secondary gap-1.5 px-3 py-2 text-sm">
                                <x-lucide-trash-2 class="h-4 w-4 shrink-0" aria-hidden="true" />
                                Hapus
                            </button>
                        </template>
                        <template x-if="armed">
                            <div class="flex w-full flex-wrap items-center gap-2 rounded-md border border-red-200 bg-red-50/40 p-2">
                                <span class="text-xs text-ink/80">Hapus lomba beserta {{ $competition->registrations_count }} pendaftaran?</span>
                                <button type="button" @click="armed = false" class="siperlo-btn-secondary !px-3 py-2 text-sm">Batal</button>
                                <form method="POST" action="{{ route('admin.competitions.destroy', $competition) }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-submit-button variant="danger" label="Konfirmasi Hapus" pending-label="Menghapus..." class="!px-3" />
                                </form>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-sm text-muted-ink">
                Belum ada lomba yang dibuat.
                <a href="{{ route('admin.competitions.create') }}" class="font-semibold text-campus-green underline">Tambah lomba pertama</a>.
            </div>
        @endforelse
    </div>

    <div class="border-t border-border-line p-4">{{ $competitions->links() }}</div>
</section>
@endsection
