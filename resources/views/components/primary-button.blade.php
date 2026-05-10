{{-- Delegates ke .siperlo-btn-primary agar konsisten dengan tombol submit di seluruh app. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'siperlo-btn-primary px-4 py-2 text-sm']) }}>
    {{ $slot }}
</button>
