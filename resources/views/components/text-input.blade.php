@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'siperlo-field block w-full px-3 py-2']) }}>
