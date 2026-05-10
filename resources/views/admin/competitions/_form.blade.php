@csrf

<div class="grid gap-6 xl:grid-cols-[1fr_340px]">
    <section class="space-y-5">
        <div class="siperlo-panel-muted p-4">
            <h2 class="font-display text-xl font-bold">Informasi Utama</h2>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="competition-title" class="text-sm font-semibold">Nama Lomba <span class="siperlo-required" aria-hidden="true">*</span></label>
                    <input id="competition-title" name="title" value="{{ old('title', $competition->title) }}" class="siperlo-field mt-1 w-full" required
                           aria-invalid="@error('title') true @else false @enderror"
                           @error('title') aria-describedby="competition-title-error" @enderror>
                    @error('title')
                        <div id="competition-title-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-organizer" class="text-sm font-semibold">Penyelenggara <span class="siperlo-required" aria-hidden="true">*</span></label>
                    <input id="competition-organizer" name="organizer" value="{{ old('organizer', $competition->organizer) }}" class="siperlo-field mt-1 w-full" required
                           aria-invalid="@error('organizer') true @else false @enderror"
                           @error('organizer') aria-describedby="competition-organizer-error" @enderror>
                    @error('organizer')
                        <div id="competition-organizer-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-category" class="text-sm font-semibold">Kategori <span class="siperlo-required" aria-hidden="true">*</span></label>
                    <select id="competition-category" name="category" class="siperlo-field mt-1 w-full" required
                            aria-invalid="@error('category') true @else false @enderror"
                            @error('category') aria-describedby="competition-category-error" @enderror>
                        <option value="">Pilih kategori</option>
                        @foreach (\App\Models\Competition::CATEGORIES as $value => $label)
                            <option value="{{ $value }}" @selected(old('category', $competition->category) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <div id="competition-category-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-type" class="text-sm font-semibold">Tipe</label>
                    <select id="competition-type" name="type" class="siperlo-field mt-1 w-full"
                            aria-invalid="@error('type') true @else false @enderror"
                            @error('type') aria-describedby="competition-type-error" @enderror>
                        <option value="">Pilih tipe</option>
                        @foreach (\App\Models\Competition::TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $competition->type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <div id="competition-type-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-status" class="text-sm font-semibold">Status <span class="siperlo-required" aria-hidden="true">*</span></label>
                    <select id="competition-status" name="status" class="siperlo-field mt-1 w-full" required
                            aria-invalid="@error('status') true @else false @enderror"
                            @error('status') aria-describedby="competition-status-error" @enderror>
                        @foreach (['open' => 'Pendaftaran Buka', 'soon' => 'Akan Datang', 'closed' => 'Ditutup', 'draft' => 'Draft'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $competition->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <div id="competition-status-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="competition-description" class="text-sm font-semibold">Deskripsi</label>
                    <textarea id="competition-description" name="description" rows="6" class="siperlo-field mt-1 w-full" placeholder="Ringkas, jelas, dan cukup untuk membantu mahasiswa memahami lomba."
                              aria-invalid="@error('description') true @else false @enderror"
                              @error('description') aria-describedby="competition-description-error" @enderror>{{ old('description', $competition->description) }}</textarea>
                    @error('description')
                        <div id="competition-description-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="siperlo-panel-muted p-4">
            <h2 class="font-display text-xl font-bold">Jadwal dan Kebutuhan</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label for="competition-deadline" class="text-sm font-semibold">Deadline Pendaftaran <span class="siperlo-required" aria-hidden="true">*</span></label>
                    <input id="competition-deadline" type="datetime-local" name="registration_deadline" value="{{ old('registration_deadline', optional($competition->registration_deadline)->format('Y-m-d\TH:i')) }}" class="siperlo-field mt-1 w-full" required
                           aria-invalid="@error('registration_deadline') true @else false @enderror"
                           @error('registration_deadline') aria-describedby="competition-deadline-error" @enderror>
                    @error('registration_deadline')
                        <div id="competition-deadline-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-location" class="text-sm font-semibold">Lokasi</label>
                    <input id="competition-location" name="location" value="{{ old('location', $competition->location) }}" class="siperlo-field mt-1 w-full" placeholder="Online, Jakarta, atau lokasi penyelenggara"
                           aria-invalid="@error('location') true @else false @enderror"
                           @error('location') aria-describedby="competition-location-error" @enderror>
                    @error('location')
                        <div id="competition-location-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-event-start" class="text-sm font-semibold">Mulai Event</label>
                    <input id="competition-event-start" type="datetime-local" name="event_start" value="{{ old('event_start', optional($competition->event_start)->format('Y-m-d\TH:i')) }}" class="siperlo-field mt-1 w-full"
                           aria-invalid="@error('event_start') true @else false @enderror"
                           @error('event_start') aria-describedby="competition-event-start-error" @enderror>
                    @error('event_start')
                        <div id="competition-event-start-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-event-end" class="text-sm font-semibold">Selesai Event</label>
                    <input id="competition-event-end" type="datetime-local" name="event_end" value="{{ old('event_end', optional($competition->event_end)->format('Y-m-d\TH:i')) }}" class="siperlo-field mt-1 w-full"
                           aria-invalid="@error('event_end') true @else false @enderror"
                           @error('event_end') aria-describedby="competition-event-end-error" @enderror>
                    @error('event_end')
                        <div id="competition-event-end-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-fee" class="text-sm font-semibold">Biaya</label>
                    <input id="competition-fee" type="number" name="fee" value="{{ old('fee', $competition->fee) }}" min="0" class="siperlo-field mt-1 w-full"
                           aria-invalid="@error('fee') true @else false @enderror"
                           @error('fee') aria-describedby="competition-fee-error" @enderror>
                    @error('fee')
                        <div id="competition-fee-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="siperlo-panel-muted p-4">
            <h2 class="font-display text-xl font-bold">Konten SOP Lomba</h2>
            <div class="mt-4 grid gap-4">
                <div>
                    <label for="competition-requirements" class="text-sm font-semibold">Syarat Peserta</label>
                    <textarea id="competition-requirements" name="requirements" rows="4" class="siperlo-field mt-1 w-full" placeholder="Tulis satu syarat per baris."
                              aria-invalid="@error('requirements') true @else false @enderror"
                              @error('requirements') aria-describedby="competition-requirements-error" @enderror>{{ old('requirements', $competition->requirements) }}</textarea>
                    @error('requirements')
                        <div id="competition-requirements-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-benefits" class="text-sm font-semibold">Benefit / Hadiah</label>
                    <textarea id="competition-benefits" name="benefits" rows="4" class="siperlo-field mt-1 w-full" placeholder="Contoh: Sertifikat, SKPI, uang pembinaan."
                              aria-invalid="@error('benefits') true @else false @enderror"
                              @error('benefits') aria-describedby="competition-benefits-error" @enderror>{{ old('benefits', $competition->benefits) }}</textarea>
                    @error('benefits')
                        <div id="competition-benefits-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-timeline" class="text-sm font-semibold">Timeline Lomba</label>
                    <textarea id="competition-timeline" name="timeline" rows="5" class="siperlo-field mt-1 w-full" placeholder="Contoh: 10 Mei - Pendaftaran dibuka"
                              aria-invalid="@error('timeline') true @else false @enderror"
                              @error('timeline') aria-describedby="competition-timeline-error" @enderror>{{ old('timeline', $competition->timeline) }}</textarea>
                    @error('timeline')
                        <div id="competition-timeline-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </section>

    <aside class="space-y-5">
        <div class="siperlo-panel-muted p-4">
            <h2 class="font-display text-lg font-bold">Dokumen</h2>
            <div class="mt-4 space-y-4">
                <div>
                    <label for="competition-poster" class="text-sm font-semibold">Poster</label>
                    <input id="competition-poster" type="file" name="poster_image" accept="image/jpeg,image/png,image/webp" class="siperlo-file mt-1 w-full"
                           aria-describedby="competition-poster-help @error('poster_image') competition-poster-error @enderror"
                           aria-invalid="@error('poster_image') true @else false @enderror">
                    <p id="competition-poster-help" class="mt-1 text-xs text-muted-ink">JPG, PNG, atau WebP. Maksimal 2 MB.</p>
                    @if ($competition->poster_image)
                        <p class="mt-1 text-xs font-semibold text-campus-green">Poster saat ini sudah tersimpan.</p>
                    @endif
                    @error('poster_image')
                        <div id="competition-poster-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-guidebook" class="text-sm font-semibold">Guidebook PDF</label>
                    <input id="competition-guidebook" type="file" name="guidebook_file" accept="application/pdf" class="siperlo-file mt-1 w-full"
                           aria-describedby="competition-guidebook-help @error('guidebook_file') competition-guidebook-error @enderror"
                           aria-invalid="@error('guidebook_file') true @else false @enderror">
                    <p id="competition-guidebook-help" class="mt-1 text-xs text-muted-ink">PDF. Maksimal 5 MB.</p>
                    @if ($competition->guidebook_file)
                        <p class="mt-1 text-xs font-semibold text-campus-green">Guidebook saat ini sudah tersimpan.</p>
                    @endif
                    @error('guidebook_file')
                        <div id="competition-guidebook-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="siperlo-panel-muted p-4">
            <h2 class="font-display text-lg font-bold">Kontak dan Link</h2>
            <div class="mt-4 space-y-4">
                <div>
                    <label for="competition-contact-name" class="text-sm font-semibold">Nama Contact Person</label>
                    <input id="competition-contact-name" name="contact_person_name" value="{{ old('contact_person_name', $competition->contact_person_name) }}" class="siperlo-field mt-1 w-full"
                           aria-invalid="@error('contact_person_name') true @else false @enderror"
                           @error('contact_person_name') aria-describedby="competition-contact-name-error" @enderror>
                    @error('contact_person_name')
                        <div id="competition-contact-name-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-contact-phone" class="text-sm font-semibold">Nomor Contact Person</label>
                    <input id="competition-contact-phone" name="contact_person_phone" value="{{ old('contact_person_phone', $competition->contact_person_phone) }}" class="siperlo-field mt-1 w-full" placeholder="Contoh: 081234567890"
                           aria-invalid="@error('contact_person_phone') true @else false @enderror"
                           @error('contact_person_phone') aria-describedby="competition-contact-phone-error" @enderror>
                    @error('contact_person_phone')
                        <div id="competition-contact-phone-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-contact-email" class="text-sm font-semibold">Email Contact Person</label>
                    <input id="competition-contact-email" type="email" name="contact_person_email" value="{{ old('contact_person_email', $competition->contact_person_email) }}" class="siperlo-field mt-1 w-full"
                           aria-invalid="@error('contact_person_email') true @else false @enderror"
                           @error('contact_person_email') aria-describedby="competition-contact-email-error" @enderror>
                    @error('contact_person_email')
                        <div id="competition-contact-email-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-official-website" class="text-sm font-semibold">Website Resmi</label>
                    <input id="competition-official-website" type="url" name="official_website" value="{{ old('official_website', $competition->official_website) }}" class="siperlo-field mt-1 w-full" placeholder="https://..."
                           aria-invalid="@error('official_website') true @else false @enderror"
                           @error('official_website') aria-describedby="competition-official-website-error" @enderror>
                    @error('official_website')
                        <div id="competition-official-website-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-social-media" class="text-sm font-semibold">Sosial Media Penyelenggara</label>
                    <input id="competition-social-media" type="url" name="social_media" value="{{ old('social_media', $competition->social_media) }}" class="siperlo-field mt-1 w-full" placeholder="https://instagram.com/..."
                           aria-invalid="@error('social_media') true @else false @enderror"
                           @error('social_media') aria-describedby="competition-social-media-error" @enderror>
                    @error('social_media')
                        <div id="competition-social-media-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="competition-external-registration" class="text-sm font-semibold">Link Pendaftaran Eksternal</label>
                    <input id="competition-external-registration" type="url" name="external_registration_url" value="{{ old('external_registration_url', $competition->external_registration_url) }}" class="siperlo-field mt-1 w-full" placeholder="https://..."
                           aria-invalid="@error('external_registration_url') true @else false @enderror"
                           @error('external_registration_url') aria-describedby="competition-external-registration-error" @enderror>
                    @error('external_registration_url')
                        <div id="competition-external-registration-error" class="siperlo-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="siperlo-surface rounded-md p-4 lg:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ route('admin.competitions.index') }}" class="siperlo-btn-secondary px-5 py-2 text-sm">Batal</a>
                <x-submit-button label="Simpan Lomba" pending-label="Menyimpan..." class="!px-5" />
            </div>
        </div>
    </aside>
</div>
