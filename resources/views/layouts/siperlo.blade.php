<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIPERLO UPNVJ')</title>
    <link rel="preload" as="font" type="font/woff2" href="/fonts/atkinson-hyperlegible-latin-400-normal.woff2" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="/fonts/literata-latin-700-normal.woff2" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
@php
    $user = auth()->user();
    $nav = [];
    $bottomNav = [];

    // Icon Lucide per route. Pakai slug name yang cocok dengan <x-lucide-...>.
    $iconFor = [
        'admin.dashboard' => 'gauge',
        'admin.competitions.index' => 'trophy',
        'admin.registrations.index' => 'clipboard-list',
        'admin.mentor-requests.index' => 'user-round-check',
        'admin.fund-requests.index' => 'coins',
        'sop.index' => 'book-open-text',
        'pimpinan.dashboard' => 'gauge',
        'competitions.index' => 'trophy',
        'mentor.dashboard' => 'gauge',
        'mentors.index' => 'users-round',
        'registrations.index' => 'list',
        'profile.edit' => 'circle-user',
    ];

    if ($user?->isRole('admin')) {
        $nav = [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['label' => 'Kelola Lomba', 'route' => 'admin.competitions.index'],
            ['label' => 'Review Pendaftaran', 'route' => 'admin.registrations.index'],
            ['label' => 'Review Mentor', 'route' => 'admin.mentor-requests.index'],
            ['label' => 'Review Dana', 'route' => 'admin.fund-requests.index'],
            ['label' => 'SOP', 'route' => 'sop.index'],
        ];
    } elseif ($user?->isRole('pimpinan')) {
        $nav = [
            ['label' => 'Dashboard', 'route' => 'pimpinan.dashboard'],
            ['label' => 'Lomba', 'route' => 'competitions.index'],
            ['label' => 'SOP', 'route' => 'sop.index'],
        ];
    } elseif ($user?->isRole('mentor')) {
        $nav = [
            ['label' => 'Dashboard Mentor', 'route' => 'mentor.dashboard'],
            ['label' => 'Lomba', 'route' => 'competitions.index'],
            ['label' => 'Mentor', 'route' => 'mentors.index'],
            ['label' => 'SOP', 'route' => 'sop.index'],
        ];
    } else {
        $nav = [
            ['label' => 'Daftar Lomba', 'route' => 'competitions.index'],
            ['label' => 'Lomba Saya', 'route' => 'registrations.index'],
            ['label' => 'Mentor', 'route' => 'mentors.index'],
            ['label' => 'SOP', 'route' => 'sop.index'],
            ['label' => 'Profil', 'route' => 'profile.edit'],
        ];
        $bottomNav = [
            ['label' => 'Lomba', 'route' => 'competitions.index'],
            ['label' => 'Lomba Saya', 'route' => 'registrations.index'],
            ['label' => 'Mentor', 'route' => 'mentors.index'],
            ['label' => 'Profil', 'route' => 'profile.edit'],
        ];
    }

    $isRouteActive = function (string $route): bool {
        if (request()->routeIs($route)) {
            return true;
        }
        if (str_ends_with($route, '.index')) {
            return request()->routeIs(Str::beforeLast($route, '.index').'.*');
        }
        return false;
    };
@endphp

<a href="#siperlo-main" class="siperlo-skip sr-only focus:not-sr-only">Lewati ke konten</a>

<div x-data="{
        drawer: false,
        isDesktop: window.matchMedia('(min-width: 1024px)').matches,
     }"
     x-init="
        const mql = window.matchMedia('(min-width: 1024px)');
        mql.addEventListener('change', (e) => { isDesktop = e.matches; if (e.matches) drawer = false; });
     "
     @keydown.escape.window="drawer = false"
     class="min-h-screen lg:flex">

    {{-- Mobile drawer overlay --}}
    <div x-show="drawer"
         x-transition.opacity
         @click="drawer = false"
         class="fixed inset-0 z-30 bg-ink/50 lg:hidden"
         aria-hidden="true"
         x-cloak></div>

    <aside
        x-trap.noscroll="drawer"
        :class="drawer ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-40 w-72 max-w-[85vw] transform border-r border-border-line bg-campus-green-deep text-white transition-transform duration-200 lg:sticky lg:top-0 lg:bottom-auto lg:h-screen lg:self-start lg:transform-none"
        style="transition-timing-function: var(--siperlo-ease-out-quart)"
        :aria-hidden="(isDesktop || drawer) ? 'false' : 'true'"
        aria-label="Navigasi utama">
        <div class="flex h-full flex-col"
             style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom);">
            <a href="{{ route($user->dashboardRoute()) }}"
               class="flex items-center gap-3 border-b border-white/10 px-6 py-6 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white">
                <img src="{{ asset('brand/siperlo-mark.png') }}" alt="" aria-hidden="true" decoding="async" class="h-12 w-12 rounded-md object-contain shadow-lg shadow-black/20">
                <div>
                    <div class="font-display text-xl font-bold">SIPERLO</div>
                    <div class="text-xs font-medium text-emerald-100">UPN Veteran Jakarta</div>
                </div>
            </a>

            <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-5">
                @foreach ($nav as $item)
                    @php
                        $isActive = $isRouteActive($item['route']);
                        $icon = $iconFor[$item['route']] ?? 'list';
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       @click="drawer = false"
                       @if ($isActive) aria-current="page" @endif
                       class="{{ $isActive ? 'border-campus-gold/70 bg-white text-campus-green shadow-sm' : 'border-transparent text-emerald-50 hover:border-white/10 hover:bg-white/10 hover:text-white' }} flex min-h-11 items-center gap-3 rounded-md border px-4 py-3 text-sm font-semibold transition">
                        <x-dynamic-component :component="'lucide-'.$icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 p-4">
                <div class="rounded-md border border-white/10 bg-white/10 p-3">
                    <div class="text-sm font-semibold">{{ $user->name }}</div>
                    <div class="mt-1 text-xs uppercase text-emerald-100">{{ $user->role }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="min-h-11 w-full rounded-md border border-white/15 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main id="siperlo-main" class="min-h-screen flex-1" style="padding-bottom: calc(env(safe-area-inset-bottom) + {{ $bottomNav ? '4.5rem' : '0px' }});">
        <header class="sticky top-0 z-20 border-b border-border-line bg-paper">
            <div class="flex min-h-16 items-center gap-3 px-4 py-3 sm:px-5 lg:px-8">
                <button type="button"
                        @click="drawer = true"
                        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md border border-border-line bg-panel text-ink hover:bg-hover-green-surface focus-visible:outline focus-visible:outline-2 focus-visible:outline-campus-green focus-visible:outline-offset-2 lg:hidden"
                        aria-label="Buka menu navigasi"
                        :aria-expanded="drawer.toString()">
                    <x-lucide-menu class="h-5 w-5" aria-hidden="true" />
                </button>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-xs font-semibold uppercase text-muted-ink">@yield('eyebrow', 'SIPERLO UPNVJ')</div>
                    <h1 class="truncate font-display text-xl font-bold sm:text-2xl">@yield('page_title', 'Dashboard')</h1>
                    @hasSection('breadcrumb')
                        @yield('breadcrumb')
                    @endif
                </div>
                <div class="hidden text-right text-sm text-muted-ink sm:block">
                    <div>{{ now()->translatedFormat('d F Y') }}</div>
                    <div class="font-medium text-ink/80">Layanan Perlombaan Mahasiswa</div>
                </div>
            </div>
        </header>

        <div class="siperlo-reveal mx-auto max-w-[1400px] px-4 py-5 sm:px-5 lg:px-8 lg:py-6">
            @if (session('success'))
                <div role="status" aria-live="polite" class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div role="alert" class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <div class="font-semibold">Ada data yang perlu diperbaiki.</div>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @if ($bottomNav)
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-border-line bg-paper lg:hidden"
             style="padding-bottom: env(safe-area-inset-bottom);"
             aria-label="Navigasi cepat">
            <div class="grid" style="grid-template-columns: repeat({{ count($bottomNav) }}, minmax(0, 1fr));">
                @foreach ($bottomNav as $item)
                    @php
                        $isActive = $isRouteActive($item['route']);
                        $icon = $iconFor[$item['route']] ?? 'list';
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       @if ($isActive) aria-current="page" @endif
                       class="{{ $isActive ? 'text-campus-green' : 'text-muted-ink hover:text-campus-green' }} flex min-h-14 flex-col items-center justify-center gap-1 px-2 py-2 text-xs font-semibold">
                        <x-dynamic-component :component="'lucide-'.$icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </nav>
    @endif
</div>

<style>[x-cloak]{display:none !important}</style>
@stack('scripts')
</body>
</html>
