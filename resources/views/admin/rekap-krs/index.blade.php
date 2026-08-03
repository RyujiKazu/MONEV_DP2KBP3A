@extends('layouts.app')

@section('title', 'Data KRS')

@section('content')
    <div class="mx-auto max-w-[96rem] space-y-6">
        <header class="rounded-[2rem] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_80px_rgba(15,23,42,0.10)] sm:px-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="text-sm font-semibold tracking-[0.22em] text-[#1f4b75] uppercase">Master Data</p><h1 class="mt-3 text-3xl font-semibold text-[#1f3550] sm:text-4xl">Data KRS</h1><p class="mt-4 max-w-3xl text-sm leading-7 text-slate-600">Kelola rekap keluarga berisiko stunting pada tingkat kecamatan dan periode tahun.</p></div>
                <a href="{{ route('admin.rekap-krs.create') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#1f4b75] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#173a5c] focus:outline-none focus:ring-4 focus:ring-[#b9cddd]">Tambah Data KRS</a>
            </div>
        </header>

        <x-flash-messages />

        <section class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="filter-krs-title">
            <h2 id="filter-krs-title" class="text-lg font-semibold text-slate-900">Pencarian dan pengurutan</h2>
            <form method="get" action="{{ route('admin.rekap-krs.index') }}" class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-[1.4fr_0.7fr_0.7fr_0.65fr_auto]">
                <div><label for="search" class="mb-2 block text-sm font-medium text-slate-700">Cari Kecamatan</label><input id="search" name="search" type="search" value="{{ $filters['search'] }}" placeholder="Nama atau kode kecamatan" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]"></div>
                <div><label for="tahun" class="mb-2 block text-sm font-medium text-slate-700">Tahun</label><select id="tahun" name="tahun" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]"><option value="">Semua Tahun</option>@foreach($tahunOptions as $tahun)<option value="{{ $tahun }}" @selected((int) $filters['tahun'] === (int) $tahun)>{{ $tahun }}</option>@endforeach</select></div>
                <div><label for="sort" class="mb-2 block text-sm font-medium text-slate-700">Urutkan</label><select id="sort" name="sort" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">@foreach($sortOptions as $value => $label)<option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>@endforeach</select></div>
                <div><label for="direction" class="mb-2 block text-sm font-medium text-slate-700">Arah</label><select id="direction" name="direction" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]"><option value="asc" @selected($filters['direction'] === 'asc')>Naik</option><option value="desc" @selected($filters['direction'] === 'desc')>Turun</option></select></div>
                <div class="flex items-end gap-2 sm:col-span-2 xl:col-span-1"><button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#1f4b75] px-4 py-3 text-sm font-medium text-white hover:bg-[#173a5c] focus:outline-none focus:ring-4 focus:ring-[#b9cddd]">Terapkan</button><a href="{{ route('admin.rekap-krs.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Reset</a></div>
            </form>
        </section>

        <section class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm" aria-labelledby="daftar-krs-title">
            <div class="border-b border-slate-200 px-6 py-5"><h2 id="daftar-krs-title" class="text-lg font-semibold text-slate-900">Daftar Rekap KRS</h2><p class="mt-1 text-sm text-slate-500">{{ $rekapKrs->total() }} data ditemukan.</p></div>
            @if ($rekapKrs->isEmpty())
                <div class="p-6"><x-empty-state title="Data KRS belum tersedia" description="Belum ada rekap yang sesuai dengan filter. Tambahkan data baru atau ubah filter pencarian." /></div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-[82rem] divide-y divide-slate-200 text-left text-sm">
                        <caption class="sr-only">Daftar rekap KRS kecamatan</caption>
                        <thead class="bg-slate-50 text-slate-600"><tr><th scope="col" class="px-5 py-3 font-medium">Kecamatan</th><th scope="col" class="px-5 py-3 font-medium">Tahun</th><th scope="col" class="px-5 py-3 font-medium">Status dan Sumber</th><th scope="col" class="px-5 py-3 font-medium">Keluarga</th><th scope="col" class="px-5 py-3 font-medium">Sasaran</th><th scope="col" class="px-5 py-3 font-medium">Total KRS</th><th scope="col" class="px-5 py-3 font-medium">Persentase KRS</th><th scope="col" class="px-5 py-3 font-medium">Dibuat Oleh</th><th scope="col" class="px-5 py-3 font-medium">Aksi</th></tr></thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($rekapKrs as $item)
                                @php($krsPercentage = $item->jumlah_keluarga_sasaran > 0 ? ($item->total_krs / $item->jumlah_keluarga_sasaran) * 100 : null)
                                <tr class="hover:bg-slate-50/70"><th scope="row" class="px-5 py-4 font-semibold text-slate-900"><span class="block">{{ $item->kecamatan?->nama_kecamatan ?? '-' }}</span><span class="mt-1 block text-xs font-normal text-slate-500">{{ $item->kode_kecamatan }}</span></th><td class="px-5 py-4 text-slate-700">{{ $item->tahun }}</td><td class="px-5 py-4"><x-status-badge :status="$item->is_simulasi ? 'Simulasi' : 'Aktual'" /><span class="mt-2 block max-w-64 text-xs leading-5 text-slate-500">{{ $item->sumber_data ?: 'Sumber tidak dicantumkan' }}</span></td><td class="px-5 py-4 text-slate-600">{{ number_format($item->jumlah_keluarga, 0, ',', '.') }}</td><td class="px-5 py-4 text-slate-600">{{ number_format($item->jumlah_keluarga_sasaran, 0, ',', '.') }}</td><td class="px-5 py-4 font-semibold text-[#1f3550]">{{ number_format($item->total_krs, 0, ',', '.') }}</td><td class="px-5 py-4 text-slate-700">{{ $krsPercentage === null ? 'Data Tidak Tersedia' : number_format($krsPercentage, 2, ',', '.').'%' }}</td><td class="px-5 py-4 text-slate-600">{{ $item->pembuat?->nama_lengkap ?? 'Tidak tercatat' }}</td><td class="px-5 py-4"><div class="flex flex-wrap gap-2"><a href="{{ route('admin.rekap-krs.show', $item) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Detail</a><a href="{{ route('admin.rekap-krs.edit', $item) }}" class="rounded-lg border border-blue-200 px-3 py-2 text-xs font-medium text-[#1f4b75] hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Ubah</a><form action="{{ route('admin.rekap-krs.destroy', $item) }}" method="post" onsubmit="return confirm('Yakin ingin menghapus data KRS ini?')">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-100">Hapus</button></form></div></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-200 px-5 py-4">{{ $rekapKrs->links() }}</div>
            @endif
        </section>
    </div>
@endsection
