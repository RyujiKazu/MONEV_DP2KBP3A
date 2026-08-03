@extends('layouts.app')

@section('title', 'Detail Data KRS')

@php
    $detailSections = [
        ['title' => 'B. Data Keluarga', 'fields' => ['jumlah_keluarga' => 'Jumlah Keluarga', 'jumlah_keluarga_sasaran' => 'Jumlah Keluarga Sasaran', 'total_krs' => 'Total KRS', 'tidak_berisiko' => 'Tidak Berisiko']],
        ['title' => 'C. Peringkat Kesejahteraan', 'fields' => ['kesejahteraan_1' => 'Peringkat 1', 'kesejahteraan_2' => 'Peringkat 2', 'kesejahteraan_3' => 'Peringkat 3', 'kesejahteraan_4' => 'Peringkat 4', 'kesejahteraan_lebih_4' => 'Lebih dari 4']],
        ['title' => 'D. Sasaran Keluarga', 'fields' => ['baduta' => 'Baduta', 'balita' => 'Balita', 'pus' => 'PUS', 'pus_hamil' => 'PUS Hamil']],
        ['title' => 'E. Faktor Lingkungan', 'fields' => ['air_minum_tidak_layak' => 'Air Minum Tidak Layak', 'jamban_tidak_layak' => 'Jamban Tidak Layak']],
        ['title' => 'F. PUS 4 Terlalu', 'fields' => ['terlalu_muda' => 'Terlalu Muda', 'terlalu_tua' => 'Terlalu Tua', 'terlalu_dekat' => 'Terlalu Dekat', 'terlalu_banyak' => 'Terlalu Banyak', 'jumlah_4t' => 'Jumlah Unik PUS 4 Terlalu']],
    ];
    $formatInteger = static fn (int|float|string|null $value): string => $value === null
        ? 'Data Tidak Tersedia'
        : number_format((float) $value, 0, ',', '.');
@endphp

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        <header class="rounded-[2rem] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_80px_rgba(15,23,42,0.10)] sm:px-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-sm font-semibold tracking-[0.22em] text-[#1f4b75] uppercase">Detail Data KRS</p><div class="mt-3 flex flex-wrap items-center gap-3"><h1 class="text-3xl font-semibold text-[#1f3550] sm:text-4xl">{{ $rekapKrs->kecamatan?->nama_kecamatan ?? $rekapKrs->kode_kecamatan }}</h1><x-status-badge :status="$rekapKrs->is_simulasi ? 'Simulasi' : 'Aktual'" /></div><p class="mt-3 text-sm text-slate-600">Tahun {{ $rekapKrs->tahun }} &middot; Kode {{ $rekapKrs->kode_kecamatan }}</p></div><div class="flex flex-wrap gap-2"><a href="{{ route('admin.rekap-krs.index') }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Kembali</a><a href="{{ route('admin.rekap-krs.edit', $rekapKrs) }}" class="rounded-xl bg-[#1f4b75] px-4 py-3 text-sm font-medium text-white hover:bg-[#173a5c] focus:outline-none focus:ring-4 focus:ring-[#b9cddd]">Ubah Data</a></div></div>
        </header>
        <x-flash-messages />
        <section class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm"><h2 class="text-lg font-semibold text-slate-900">A. Wilayah, Periode, dan Sumber</h2><dl class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"><div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Kecamatan</dt><dd class="mt-2 font-semibold text-slate-900">{{ $rekapKrs->kecamatan?->nama_kecamatan ?? '-' }}</dd></div><div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Tahun dan status</dt><dd class="mt-2 flex flex-wrap items-center gap-2 font-semibold text-slate-900"><span>{{ $rekapKrs->tahun }}</span><x-status-badge :status="$rekapKrs->is_simulasi ? 'Simulasi' : 'Aktual'" /></dd></div><div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Dibuat oleh</dt><dd class="mt-2 font-semibold text-slate-900">{{ $rekapKrs->pembuat?->nama_lengkap ?? 'Tidak tercatat' }}</dd></div><div class="rounded-xl bg-slate-50 p-4 sm:col-span-2"><dt class="text-xs text-slate-500">Sumber data</dt><dd class="mt-2 text-sm font-semibold leading-6 text-slate-900">{{ $rekapKrs->sumber_data ?: 'Sumber tidak dicantumkan' }}</dd></div><div class="rounded-xl bg-slate-50 p-4 sm:col-span-2 lg:col-span-1"><dt class="text-xs text-slate-500">Catatan data</dt><dd class="mt-2 text-sm leading-6 text-slate-700">{{ $rekapKrs->catatan_data ?: 'Tidak ada catatan' }}</dd></div></dl></section>
        @if ($rekapKrs->is_simulasi)
            <aside class="rounded-[1.5rem] border border-amber-300 bg-amber-50 p-5 text-sm leading-6 text-amber-950"><p class="font-semibold">Data simulasi sementara</p><p class="mt-1">Data ini digunakan untuk pengujian sistem dan bukan data aktual/resmi. Ganti dengan data resmi setelah tersedia.</p></aside>
        @endif
        @foreach ($detailSections as $section)
            <section class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm"><h2 class="text-lg font-semibold text-slate-900">{{ $section['title'] }}</h2><dl class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@foreach($section['fields'] as $field => $label)<div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs leading-5 text-slate-500">{{ $label }}</dt><dd class="mt-2 {{ $rekapKrs->getAttribute($field) === null ? 'text-sm' : 'text-xl' }} font-semibold text-[#1f3550]">{{ $formatInteger($rekapKrs->getAttribute($field)) }}</dd></div>@endforeach</dl></section>
        @endforeach
        <aside class="rounded-[1.5rem] border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-900">Kategori Baduta, Balita, PUS, dan PUS Hamil dapat saling beririsan. Empat subkategori 4T tetap ditampilkan sebagai informasi, tetapi tidak dijumlahkan untuk menentukan jumlah unik PUS 4T. Jika total unik tidak tersedia, KPI-04 ditampilkan sebagai <strong>Data Tidak Tersedia</strong>, bukan 0%.</aside>
        <div class="flex justify-end"><form action="{{ route('admin.rekap-krs.destroy', $rekapKrs) }}" method="post" onsubmit="return confirm('Yakin ingin menghapus data KRS ini?')">@csrf @method('DELETE')<button type="submit" class="rounded-xl border border-red-300 px-5 py-3 text-sm font-medium text-red-700 hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-100">Hapus Data KRS</button></form></div>
    </div>
@endsection
