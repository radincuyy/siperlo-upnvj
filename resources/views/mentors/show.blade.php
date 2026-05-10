@extends('layouts.siperlo')

@section('title', $mentor->user->name.' - SIPERLO')
@section('page_title', $mentor->user->name)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mentor', 'route' => 'mentors.index'],
        ['label' => $mentor->user->name],
    ]" />
@endsection

@section('content')
<div class="grid gap-6 xl:grid-cols-[1fr_360px]">
    <section class="space-y-5">
        <div class="siperlo-surface rounded-md p-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                <div class="h-28 w-28 shrink-0 overflow-hidden rounded-md border border-border-line bg-admin-note-surface">
                    <img src="{{ asset('brand/mentor-placeholder.svg') }}"
                         alt=""
                         aria-hidden="true"
                         decoding="async"
                         class="h-full w-full object-cover">
                </div>
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-campus-green">{{ $mentor->expertise }}</div>
                    <h2 class="mt-1 font-display text-2xl font-bold">{{ $mentor->user->name }}</h2>
                    <p class="mt-4 leading-7 text-ink/80">{{ $mentor->bio }}</p>
                </div>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="rounded-md border border-border-line bg-admin-note-surface p-4">
                    <div class="font-display text-2xl font-bold text-campus-green">{{ $mentor->total_mentored }}</div>
                    <div class="text-sm text-muted-ink">Total Bimbingan</div>
                </div>
                <div class="rounded-md border border-border-line bg-admin-note-surface p-4">
                    <div class="font-display text-2xl font-bold text-campus-green">{{ $mentor->achievements->count() }}</div>
                    <div class="text-sm text-muted-ink">Achievement</div>
                </div>
                <div class="rounded-md border border-border-line bg-admin-note-surface p-4">
                    <div class="font-display text-2xl font-bold text-campus-green">{{ $mentor->is_active ? 'Aktif' : 'Nonaktif' }}</div>
                    <div class="text-sm text-muted-ink">Status Mentor</div>
                </div>
            </div>
        </div>

        <div class="siperlo-surface rounded-md p-6">
            <h2 class="font-display text-xl font-bold">Riwayat Achievement</h2>
            <div class="mt-4 divide-y divide-border-line">
                @forelse ($mentor->achievements as $achievement)
                    <div class="py-3">
                        <div class="font-semibold">{{ $achievement->competition_name }}</div>
                        <div class="text-sm text-ink/80">{{ $achievement->student_name }} - {{ $achievement->result }} ({{ $achievement->year }})</div>
                    </div>
                @empty
                    <div class="py-3 text-sm text-muted-ink">Belum ada achievement tercatat.</div>
                @endforelse
            </div>
        </div>
    </section>

    <aside class="siperlo-surface rounded-md p-5">
        <h2 class="font-display text-lg font-bold">Ajukan Mentor</h2>
        <p class="mt-2 text-sm text-ink/80">Pilih lomba yang sudah kamu daftarkan, lalu jelaskan kebutuhan bimbingan. Kamu tetap bisa mengikuti lomba tanpa mentor.</p>
        @if (auth()->user()->isRole('mahasiswa') && $availableRegistrations->isNotEmpty())
            <form method="POST" action="{{ route('mentor-requests.store') }}" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="mentor_id" value="{{ $mentor->id }}">
                <div>
                    <label for="mentor-registration-id" class="text-sm font-semibold">Lomba Terdaftar <span class="siperlo-required">*</span></label>
                    <select id="mentor-registration-id" name="registration_id" class="siperlo-field mt-1 w-full" required
                            aria-invalid="@error('registration_id') true @else false @enderror"
                            @error('registration_id') aria-describedby="mentor-registration-id-error" @enderror>
                        <option value="">Pilih lomba</option>
                        @foreach ($availableRegistrations as $registration)
                            <option value="{{ $registration->id }}">{{ $registration->competition->title }}</option>
                        @endforeach
                    </select>
                    @error('registration_id')
                        <div id="mentor-registration-id-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label for="mentor-reason" class="text-sm font-semibold">Alasan <span class="siperlo-required">*</span></label>
                    <textarea id="mentor-reason" name="reason" rows="5" class="siperlo-field mt-1 w-full" placeholder="Contoh: Membutuhkan arahan strategi dan latihan presentasi." required
                              aria-invalid="@error('reason') true @else false @enderror"
                              @error('reason') aria-describedby="mentor-reason-error" @enderror>{{ old('reason') }}</textarea>
                    @error('reason')
                        <div id="mentor-reason-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>
                <x-submit-button label="Kirim Pengajuan" pending-label="Mengirim pengajuan..." class="w-full" />
            </form>
        @elseif (auth()->user()->isRole('mahasiswa'))
            <div class="mt-4 rounded-md border border-border-line bg-admin-note-surface p-3 text-sm text-ink/80">Tidak ada lomba aktif yang bisa diajukan mentor.</div>
        @else
            <div class="mt-4 rounded-md border border-border-line bg-admin-note-surface p-3 text-sm text-ink/80">Pengajuan mentor hanya tersedia untuk mahasiswa.</div>
        @endif
    </aside>
</div>
@endsection
