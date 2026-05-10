@props([
    'variant' => 'primary',
    'label' => 'Simpan',
    'pendingLabel' => 'Mengirim...',
])

@php
    $variantClass = match ($variant) {
        'secondary' => 'siperlo-btn-secondary',
        'danger' => 'siperlo-btn-danger',
        default => 'siperlo-btn-primary',
    };
@endphp

{{-- Anti double-submit button: disables itself while form submission is in flight. --}}
<button
    x-data="{ sending: false }"
    x-init="$el.closest('form')?.addEventListener('submit', () => { sending = true; })"
    :disabled="sending"
    :aria-busy="sending"
    {{ $attributes->class([$variantClass, 'px-4 py-2 text-sm']) }}
>
    <span x-show="!sending">{{ $label }}</span>
    <span x-show="sending" x-cloak class="inline-flex items-center gap-2">
        <x-lucide-loader-circle class="h-4 w-4 animate-spin" aria-hidden="true" />
        <span>{{ $pendingLabel }}</span>
    </span>
</button>
