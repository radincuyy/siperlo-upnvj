@extends('layouts.siperlo')

@section('title', 'Pengajuan Dana - SIPERLO')
@section('page_title', 'Pengajuan Dana')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Lomba Saya', 'route' => 'registrations.index'],
        ['label' => 'Pengajuan Bantuan Dana'],
    ]" />
@endsection

@section('content')
<form method="POST" action="{{ route('fund-requests.store') }}" enctype="multipart/form-data" class="siperlo-surface mx-auto max-w-3xl rounded-md p-6">
    @csrf
    <p class="text-sm text-ink/80">
        Ajukan dana hanya jika lomba membutuhkan dukungan biaya. Pengajuan ini <span class="font-semibold text-ink">opsional</span>: lomba tetap tercatat di SIPERLO walaupun tidak ada pengajuan dana.
    </p>

    @if ($registrations->isEmpty())
        <div class="mt-5 rounded-md border border-border-line bg-admin-note-surface p-4 text-sm text-ink/80">
            Tidak ada lomba aktif yang bisa diajukan bantuan dana.
        </div>
        <a href="{{ route('registrations.index') }}" class="siperlo-btn-secondary mt-5 inline-flex px-4 py-2 text-sm">Kembali ke Lomba Saya</a>
    @else
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="registration_id" class="text-sm font-semibold">Lomba Terdaftar <span class="siperlo-required">*</span></label>
                <select id="registration_id" name="registration_id" class="siperlo-field mt-1 w-full" required
                        aria-invalid="@error('registration_id') true @else false @enderror"
                        @error('registration_id') aria-describedby="registration_id-error" @enderror>
                    <option value="">Pilih lomba</option>
                    @foreach ($registrations as $registration)
                        <option value="{{ $registration->id }}" @selected($selectedRegistrationId === $registration->id)>{{ $registration->competition->title }}</option>
                    @endforeach
                </select>
                @error('registration_id')
                    <div id="registration_id-error" class="siperlo-error">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="amount" class="text-sm font-semibold">Nominal Pengajuan <span class="siperlo-required">*</span></label>
                <input id="amount" type="number" name="amount" min="1" value="{{ old('amount') }}" class="siperlo-field mt-1 w-full" placeholder="Contoh: 1250000" required
                       aria-invalid="@error('amount') true @else false @enderror"
                       @error('amount') aria-describedby="amount-error" @enderror>
                @error('amount')
                    <div id="amount-error" class="siperlo-error">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="purpose" class="text-sm font-semibold">Tujuan Dana <span class="siperlo-required">*</span></label>
                <input id="purpose" name="purpose" value="{{ old('purpose') }}" class="siperlo-field mt-1 w-full" placeholder="Contoh: Biaya registrasi dan transportasi" required
                       aria-invalid="@error('purpose') true @else false @enderror"
                       @error('purpose') aria-describedby="purpose-error" @enderror>
                @error('purpose')
                    <div id="purpose-error" class="siperlo-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="md:col-span-2">
                <label for="description" class="text-sm font-semibold">Deskripsi</label>
                <textarea id="description" name="description" rows="5" class="siperlo-field mt-1 w-full" placeholder="Jelaskan kebutuhan dana secara singkat dan spesifik."
                          aria-invalid="@error('description') true @else false @enderror"
                          @error('description') aria-describedby="description-error" @enderror>{{ old('description') }}</textarea>
                @error('description')
                    <div id="description-error" class="siperlo-error">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="proposal_file" class="text-sm font-semibold">Proposal</label>
                <input id="proposal_file" type="file" name="proposal_file" class="siperlo-file mt-1 w-full"
                       aria-describedby="proposal_file-help @error('proposal_file') proposal_file-error @enderror"
                       aria-invalid="@error('proposal_file') true @else false @enderror">
                <p id="proposal_file-help" class="mt-1 text-xs text-muted-ink">PDF, DOC, atau DOCX. Maksimal 4 MB.</p>
                @error('proposal_file')
                    <div id="proposal_file-error" class="siperlo-error">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="supporting_docs" class="text-sm font-semibold">Dokumen Pendukung</label>
                <input id="supporting_docs" type="file" name="supporting_docs" class="siperlo-file mt-1 w-full"
                       aria-describedby="supporting_docs-help @error('supporting_docs') supporting_docs-error @enderror"
                       aria-invalid="@error('supporting_docs') true @else false @enderror">
                <p id="supporting_docs-help" class="mt-1 text-xs text-muted-ink">PDF, DOC, DOCX, JPG, JPEG, atau PNG. Maksimal 4 MB.</p>
                @error('supporting_docs')
                    <div id="supporting_docs-error" class="siperlo-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <x-submit-button label="Kirim Pengajuan Dana" pending-label="Mengirim pengajuan..." class="!px-5" />
            <a href="{{ route('sop.index') }}" class="siperlo-btn-secondary px-5 py-2 text-sm">Lihat SOP Lengkap</a>
        </div>
    @endif
</form>
@endsection
