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
    <div class="flex min-h-screen items-center justify-center bg-paper px-4 py-10 sm:px-6">
        <div class="grid w-full max-w-5xl overflow-hidden rounded-2xl bg-panel shadow-2xl shadow-black/10 ring-1 ring-border-line lg:grid-cols-2">
            <div class="flex items-center p-8 sm:p-10 lg:p-12">
                <div class="w-full">
                    {{ $slot }}
                </div>
            </div>

            <div class="relative hidden min-h-[560px] overflow-hidden lg:block">
                <img src="{{ asset('brand/upnvj.webp') }}"
                     alt=""
                     aria-hidden="true"
                     class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-br from-campus-green-deep/40 via-campus-green-deep/15 to-transparent"></div>
            </div>
        </div>
    </div>
</body>
</html>
