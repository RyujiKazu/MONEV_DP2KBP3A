@extends('layouts.app')

@section('title', 'Data Wilayah')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <header class="rounded-[2rem] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_80px_rgba(15,23,42,0.10)] sm:px-8">
            <p class="text-sm font-semibold tracking-[0.22em] text-[#1f4b75] uppercase">Master Data</p>
            <h1 class="mt-3 text-3xl font-semibold text-[#1f3550] sm:text-4xl">Data Wilayah</h1>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                Tambah, ubah, dan kelola data kecamatan serta kelurahan yang menjadi acuan wilayah dalam sistem.
            </p>
        </header>

        <x-flash-messages />

        <div class="grid items-start gap-6 xl:grid-cols-2">
            <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="kecamatan-title">
                <div>
                    <p class="text-sm font-semibold text-[#1f3550]">Kecamatan</p>
                    <h2 id="kecamatan-title" class="mt-1 text-xl font-semibold text-slate-900">Kelola data kecamatan</h2>
                </div>

                <form action="{{ $editingKecamatan ? route('admin.data-wilayah.kecamatan.update', $editingKecamatan) : route('admin.data-wilayah.kecamatan.store') }}" method="post" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="_form_source" value="kecamatan">
                    @if ($editingKecamatan)
                        @method('PUT')
                    @endif

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="kode_kecamatan">Kode Kecamatan</label>
                        <input
                            id="kode_kecamatan"
                            name="kode_kecamatan"
                            type="text"
                            value="{{ old('_form_source') === 'kelurahan' ? ($editingKecamatan->kode_kecamatan ?? '') : old('kode_kecamatan', $editingKecamatan->kode_kecamatan ?? '') }}"
                            maxlength="20"
                            required
                            @disabled($editingKecamatan)
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition disabled:bg-slate-100 disabled:text-slate-500 focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]"
                            placeholder="Contoh: 32.13.01"
                        >
                        @if (old('_form_source') !== 'kelurahan')
                            @error('kode_kecamatan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @endif
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="nama_kecamatan">Nama Kecamatan</label>
                        <input id="nama_kecamatan" name="nama_kecamatan" type="text" value="{{ old('nama_kecamatan', $editingKecamatan->nama_kecamatan ?? '') }}" maxlength="100" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]" placeholder="Nama kecamatan">
                        @error('nama_kecamatan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#1f4b75] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#173a5c] focus:outline-none focus:ring-4 focus:ring-[#b9cddd]">
                            {{ $editingKecamatan ? 'Simpan Perubahan' : 'Tambah Kecamatan' }}
                        </button>
                        @if ($editingKecamatan)
                            <a href="{{ route('admin.data-wilayah.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Batal</a>
                        @endif
                    </div>
                </form>

                <div class="mt-8 overflow-hidden rounded-[1.25rem] border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-[34rem] divide-y divide-slate-200 text-left text-sm">
                            <caption class="sr-only">Daftar kecamatan</caption>
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th scope="col" class="px-5 py-3 font-medium">Kode</th>
                                    <th scope="col" class="px-5 py-3 font-medium">Nama</th>
                                    <th scope="col" class="px-5 py-3 font-medium">Kelurahan</th>
                                    <th scope="col" class="px-5 py-3 font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse ($kecamatans as $kecamatan)
                                    <tr class="transition hover:bg-slate-50/70">
                                        <td class="px-5 py-4 whitespace-nowrap text-slate-600">{{ $kecamatan->kode_kecamatan }}</td>
                                        <th scope="row" class="px-5 py-4 font-medium text-slate-900">{{ $kecamatan->nama_kecamatan }}</th>
                                        <td class="px-5 py-4 text-slate-600">{{ $kecamatan->kelurahans_count }}</td>
                                        <td class="px-5 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('admin.data-wilayah.index', ['edit_kecamatan' => $kecamatan->kode_kecamatan]) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-[#1f4b75] transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Ubah</a>
                                                <form action="{{ route('admin.data-wilayah.kecamatan.destroy', $kecamatan) }}" method="post" onsubmit="return confirm('Yakin ingin menghapus kecamatan ini? Kecamatan yang sudah memiliki data KRS tidak dapat dihapus.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-100">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-10 text-center text-slate-500">Belum ada data kecamatan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="kelurahan-title">
                <div>
                    <p class="text-sm font-semibold text-[#1f3550]">Kelurahan</p>
                    <h2 id="kelurahan-title" class="mt-1 text-xl font-semibold text-slate-900">Kelola data kelurahan</h2>
                </div>

                <form action="{{ $editingKelurahan ? route('admin.data-wilayah.kelurahan.update', $editingKelurahan) : route('admin.data-wilayah.kelurahan.store') }}" method="post" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="_form_source" value="kelurahan">
                    @if ($editingKelurahan)
                        @method('PUT')
                    @endif

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="kode_kelurahan">Kode Kelurahan</label>
                        <input id="kode_kelurahan" name="kode_kelurahan" type="text" value="{{ old('kode_kelurahan', $editingKelurahan->kode_kelurahan ?? '') }}" maxlength="20" required @disabled($editingKelurahan) class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition disabled:bg-slate-100 disabled:text-slate-500 focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]" placeholder="Contoh: 32.13.01.01">
                        @error('kode_kelurahan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="kode_kecamatan_kelurahan">Kecamatan</label>
                        @php
                            $selectedKecamatan = old('_form_source') === 'kecamatan'
                                ? ($editingKelurahan->kode_kecamatan ?? '')
                                : old('kode_kecamatan', $editingKelurahan->kode_kecamatan ?? '');
                        @endphp
                        <select id="kode_kecamatan_kelurahan" name="kode_kecamatan" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                            <option value="">Pilih kecamatan</option>
                            @foreach ($kecamatans as $kecamatan)
                                <option value="{{ $kecamatan->kode_kecamatan }}" @selected($selectedKecamatan === $kecamatan->kode_kecamatan)>
                                    {{ $kecamatan->nama_kecamatan }}
                                </option>
                            @endforeach
                        </select>
                        @if (old('_form_source') !== 'kecamatan')
                            @error('kode_kecamatan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @endif
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="nama_kelurahan">Nama Kelurahan</label>
                        <input id="nama_kelurahan" name="nama_kelurahan" type="text" value="{{ old('nama_kelurahan', $editingKelurahan->nama_kelurahan ?? '') }}" maxlength="100" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]" placeholder="Nama kelurahan">
                        @error('nama_kelurahan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#1f4b75] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#173a5c] focus:outline-none focus:ring-4 focus:ring-[#b9cddd]">
                            {{ $editingKelurahan ? 'Simpan Perubahan' : 'Tambah Kelurahan' }}
                        </button>
                        @if ($editingKelurahan)
                            <a href="{{ route('admin.data-wilayah.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Batal</a>
                        @endif
                    </div>
                </form>

                <div class="mt-8 overflow-hidden rounded-[1.25rem] border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-[36rem] divide-y divide-slate-200 text-left text-sm">
                            <caption class="sr-only">Daftar kelurahan</caption>
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th scope="col" class="px-5 py-3 font-medium">Kode</th>
                                    <th scope="col" class="px-5 py-3 font-medium">Nama</th>
                                    <th scope="col" class="px-5 py-3 font-medium">Kecamatan</th>
                                    <th scope="col" class="px-5 py-3 font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse ($kelurahans as $kelurahan)
                                    <tr class="transition hover:bg-slate-50/70">
                                        <td class="px-5 py-4 whitespace-nowrap text-slate-600">{{ $kelurahan->kode_kelurahan }}</td>
                                        <th scope="row" class="px-5 py-4 font-medium text-slate-900">{{ $kelurahan->nama_kelurahan }}</th>
                                        <td class="px-5 py-4 text-slate-600">{{ $kelurahan->kecamatan?->nama_kecamatan ?? '-' }}</td>
                                        <td class="px-5 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('admin.data-wilayah.index', ['edit_kelurahan' => $kelurahan->kode_kelurahan]) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-[#1f4b75] transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Ubah</a>
                                                <form action="{{ route('admin.data-wilayah.kelurahan.destroy', $kelurahan) }}" method="post" onsubmit="return confirm('Yakin ingin menghapus kelurahan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-100">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-10 text-center text-slate-500">Belum ada data kelurahan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
