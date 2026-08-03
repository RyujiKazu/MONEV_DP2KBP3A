<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#16283a">

        <title>@hasSection('title')@yield('title') | @endif Sistem Monev Stunting DP2KBP3A</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="min-h-screen bg-[#eef2f5] text-slate-900 antialiased">
        <a
            href="#main-content"
            class="sr-only fixed top-3 left-3 z-[70] rounded-lg bg-white px-4 py-2 text-sm font-semibold text-[#1f3550] shadow-lg focus:not-sr-only focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]"
        >
            Lewati ke konten utama
        </a>

        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 shadow-sm backdrop-blur sm:px-6 lg:hidden print:hidden">
            <a href="{{ Route::has('dashboard.index') ? route('dashboard.index') : url('/') }}" class="flex min-w-0 items-center gap-3 rounded-lg focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">
                <img src="{{ asset('Logo.jpg') }}" alt="" class="h-10 w-10 shrink-0 rounded-xl object-cover ring-1 ring-slate-200">
                <span class="min-w-0">
                    <span class="block truncate text-xs font-semibold tracking-[0.18em] text-[#1f3550] uppercase">DP2KBP3A</span>
                    <span class="block truncate text-xs text-slate-500">Kabupaten Subang</span>
                </span>
            </a>

            <button
                type="button"
                class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 text-[#1f3550] transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]"
                aria-controls="app-sidebar"
                aria-expanded="false"
                data-sidebar-open
            >
                <span class="sr-only">Buka navigasi</span>
                <svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </header>

        <button
            type="button"
            class="fixed inset-0 z-40 hidden bg-slate-950/50 backdrop-blur-[1px] lg:hidden print:hidden"
            aria-label="Tutup navigasi"
            aria-hidden="true"
            tabindex="-1"
            data-sidebar-backdrop
        ></button>

        @include('partials.sidebar')

        <main id="main-content" class="min-h-[calc(100vh-4rem)] lg:min-h-screen lg:pl-72" tabindex="-1">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>

        @stack('scripts')
    </body>
</html>
