@php
    $currentUser = auth()->user();
    $isAdmin = $currentUser?->role === 'Admin';
    $navigationItems = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard.index',
            'pattern' => 'dashboard.*',
            'admin_only' => false,
        ],
        [
            'label' => 'Data Pengguna',
            'route' => 'admin.users.index',
            'pattern' => 'admin.users.*',
            'admin_only' => true,
        ],
        [
            'label' => 'Data Wilayah',
            'route' => 'admin.data-wilayah.index',
            'pattern' => 'admin.data-wilayah.*',
            'admin_only' => true,
        ],
        [
            'label' => 'Data KRS',
            'route' => 'admin.rekap-krs.index',
            'pattern' => 'admin.rekap-krs.*',
            'admin_only' => true,
        ],
        [
            'label' => 'Target Indikator',
            'route' => 'admin.target-indikator.index',
            'pattern' => 'admin.target-indikator.*',
            'admin_only' => true,
        ],
        [
            'label' => 'Laporan Evaluasi',
            'route' => 'laporan.index',
            'pattern' => 'laporan.*',
            'admin_only' => false,
        ],
    ];
@endphp

<aside
    id="app-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col overflow-hidden bg-[#16283a] text-white shadow-2xl transition-transform duration-200 ease-out lg:translate-x-0 lg:shadow-none print:hidden"
    aria-label="Navigasi utama"
    data-sidebar
>
    <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
        <a href="{{ Route::has('dashboard.index') ? route('dashboard.index') : url('/') }}" class="flex min-w-0 flex-1 items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-white/60">
            <img src="{{ asset('Logo.jpg') }}" alt="" class="h-12 w-12 shrink-0 rounded-2xl object-cover ring-1 ring-white/20">
            <span class="min-w-0">
                <span class="block truncate text-sm font-semibold tracking-[0.18em] uppercase">DP2KBP3A</span>
                <span class="block truncate text-xs text-slate-300">Kabupaten Subang</span>
            </span>
        </a>

        <button
            type="button"
            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-slate-300 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/60 lg:hidden"
            data-sidebar-close
        >
            <span class="sr-only">Tutup navigasi</span>
            <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
            </svg>
        </button>
    </div>

    <div class="min-h-0 flex-1 overflow-y-auto px-4 py-6">
        <p class="px-2 text-xs font-semibold tracking-[0.28em] text-slate-400 uppercase">Menu</p>

        <nav class="mt-4 space-y-2">
            @foreach ($navigationItems as $item)
                @continue($item['admin_only'] && ! $isAdmin)
                @continue(! Route::has($item['route']))

                @php($isActive = request()->routeIs($item['pattern']))

                <a
                    href="{{ route($item['route']) }}"
                    class="group flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-white/60 {{ $isActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
                    @if ($isActive) aria-current="page" @endif
                >
                    <span class="h-2 w-2 shrink-0 rounded-full transition {{ $isActive ? 'bg-cyan-300' : 'bg-slate-500 group-hover:bg-slate-300' }}" aria-hidden="true"></span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="shrink-0 border-t border-white/10 px-5 py-5">
        <div class="rounded-2xl bg-white/5 p-4">
            <p class="truncate text-sm font-semibold">{{ $currentUser?->nama_lengkap ?? 'Pengguna' }}</p>
            <p class="mt-1 text-xs text-slate-300">{{ $currentUser?->role ?? '-' }}</p>
        </div>

        <form action="{{ route('logout') }}" method="post" class="mt-4">
            @csrf
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-white/10 px-4 py-3 text-sm font-medium text-white transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/60">
                Keluar
            </button>
        </form>
    </div>
</aside>
