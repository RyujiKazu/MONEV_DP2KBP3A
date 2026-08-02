@php
    $currentUser = auth()->user();
@endphp

<aside class="flex w-full flex-col overflow-hidden bg-[#16283a] text-white lg:sticky lg:top-0 lg:h-screen lg:w-72 lg:flex-none">
    <div class="flex items-center gap-3 border-b border-white/10 px-6 py-6">
        <img src="{{ asset('Logo.jpg') }}" alt="Logo DP2KBP3A" class="h-12 w-12 rounded-2xl object-cover ring-1 ring-white/20">
        <div>
            <p class="text-sm font-semibold tracking-[0.22em] uppercase">DP2KBP3A</p>
            <p class="text-xs text-slate-300">Kabupaten Subang</p>
        </div>
    </div>

    <div class="min-h-0 flex-1 overflow-y-auto px-4 py-6">
        <p class="px-2 text-xs font-semibold tracking-[0.28em] text-slate-400 uppercase">Menu</p>

        <nav class="mt-4 space-y-2">
            <a href="{{ route('admin.data-wilayah.index') }}" class="flex items-center rounded-xl px-4 py-3 text-sm transition {{ request()->routeIs('admin.data-wilayah.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                Data Wilayah
            </a>

            <a href="{{ route('admin.users.index') }}" class="flex items-center rounded-xl px-4 py-3 text-sm transition {{ request()->routeIs('admin.users.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                Kelola Pengguna
            </a>
        </nav>
    </div>

    <div class="shrink-0 border-t border-white/10 px-6 py-6">
        <div class="rounded-2xl bg-white/5 p-4">
            <p class="text-sm font-semibold">{{ $currentUser?->nama_lengkap }}</p>
            <p class="mt-1 text-xs text-slate-300">{{ $currentUser?->role }}</p>
        </div>

        <form action="{{ route('logout') }}" method="post" class="mt-4">
            @csrf
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-white/10 px-4 py-3 text-sm font-medium text-white transition hover:bg-white/15">
                Keluar
            </button>
        </form>
    </div>
</aside>