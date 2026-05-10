<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIPERLO UPNVJ') }}</title>
    <link rel="preload" as="font" type="font/woff2" href="/fonts/atkinson-hyperlegible-latin-400-normal.woff2" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="/fonts/literata-latin-700-normal.woff2" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-ink antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[1fr_560px]">
        <section class="relative hidden overflow-hidden text-white lg:flex lg:flex-col lg:justify-between">
            <img src="{{ asset('brand/upnvj.webp') }}"
                 alt="Gedung Universitas Pembangunan Nasional Veteran Jakarta"
                 class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-campus-green-deep/80"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-campus-green-deep via-campus-green/70 to-campus-green/35"></div>

            <div class="relative z-10 p-10" aria-hidden="true"></div>
            <div class="relative z-10 max-w-xl p-12">
                <div class="text-sm font-semibold uppercase text-emerald-100">SIPERLO UPNVJ</div>
                <h1 class="mt-4 font-display text-4xl font-bold">Sistem Informasi Perlombaan UPN Veteran Jakarta</h1>
                <p class="mt-5 max-w-lg leading-7 text-emerald-50">Pusat lomba, mentor, dana, dan monitoring prestasi mahasiswa.</p>
            </div>
        </section>

        <main class="flex min-h-screen items-center justify-center bg-paper px-5 py-10">
            <div class="w-full max-w-md">
                {{ $slot }}
                <p class="mt-8 text-center text-sm text-muted-ink">Akses disesuaikan otomatis berdasarkan jenis akun.</p>
            </div>
        </main>
    </div>
</body>
</html>
