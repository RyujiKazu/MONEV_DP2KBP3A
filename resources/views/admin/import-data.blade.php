<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Import Data | Sistem Monev Stunting DP2KBP3A</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#eef2f5] text-slate-900 antialiased">
        <main class="min-h-screen lg:flex">
            @include('partials.sidebar')

            <section class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_80px_rgba(15,23,42,0.10)] sm:px-8">
                        <p class="text-sm font-semibold tracking-[0.22em] text-[#1f4b75] uppercase">Menu Input</p>
                        <h1 class="mt-3 text-3xl font-semibold text-[#1f3550] sm:text-4xl">Import Data Indikator</h1>
                        <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                            Gunakan halaman ini untuk memasukkan data indikator evaluasi KRS per kecamatan atau kelurahan. Semua nilai disimpan ke tabel <span class="font-medium text-slate-900">tb_evaluasi_krs</span>.
                        </p>
                    </div>

                    @if (session('success'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            Periksa kembali isian form. Ada data yang belum valid.
                        </div>
                    @endif

                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-[#1f3550]">Import File</p>
                                <h2 class="mt-1 text-xl font-semibold text-slate-900">Unggah Excel atau CSV</h2>
                            </div>
                        </div>

                        <form action="{{ route('admin.import-data.import') }}" method="post" enctype="multipart/form-data" class="mt-6 flex flex-col gap-4 lg:flex-row lg:items-end">
                            @csrf
                            <div class="flex-1">
                                <label class="mb-2 block text-sm font-medium text-slate-700" for="import_file">File import</label>
                                <input id="import_file" name="import_file" type="file" accept=".csv,.txt,.xlsx" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition file:mr-4 file:rounded-lg file:border-0 file:bg-[#1f4b75] file:px-4 file:py-2 file:text-sm file:font-medium file:text-white focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                                @error('import_file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="lg:w-64">
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[#1f4b75] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#173a5c]">
                                    Import File
                                </button>
                            </div>
                        </form>
                        <p class="mt-3 text-xs leading-6 text-slate-500">Gunakan header kolom yang sama dengan nama field sistem, misalnya <span class="font-medium text-slate-700">kode_kecamatan</span>, <span class="font-medium text-slate-700">periode_evaluasi</span>, dan <span class="font-medium text-slate-700">total_berisiko</span>.</p>
                    </div>

                    <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                        <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-[#1f3550]">Form Input</p>
                                    <h2 class="mt-1 text-xl font-semibold text-slate-900">{{ $editingRecord ? 'Edit data indikator' : 'Tambah data indikator' }}</h2>
                                </div>
                            </div>

                            <form action="{{ $editingRecord ? route('admin.import-data.update', $editingRecord) : route('admin.import-data.store') }}" method="post" class="mt-6 space-y-6">
                                @csrf
                                @if ($editingRecord)
                                    @method('PUT')
                                @endif

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-slate-700" for="kode_kecamatan">Kecamatan</label>
                                        <select id="kode_kecamatan" name="kode_kecamatan" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                                            <option value="">Pilih kecamatan</option>
                                            @foreach ($kecamatans as $kecamatan)
                                                <option value="{{ $kecamatan->kode_kecamatan }}" @selected(old('kode_kecamatan') === $kecamatan->kode_kecamatan)>
                                                    {{ $kecamatan->nama_kecamatan }} ({{ $kecamatan->kode_kecamatan }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kode_kecamatan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-slate-700" for="kode_kelurahan">Kelurahan</label>
                                        <select id="kode_kelurahan" name="kode_kelurahan" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                                            <option value="">Rekap tingkat kecamatan</option>
                                            @foreach ($kelurahans as $kelurahan)
                                                <option value="{{ $kelurahan->kode_kelurahan }}" @selected(old('kode_kelurahan') === $kelurahan->kode_kelurahan)>
                                                    {{ $kelurahan->nama_kelurahan }} ({{ $kelurahan->kode_kelurahan }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kode_kelurahan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-slate-700" for="periode_evaluasi">Periode Evaluasi</label>
                                        <input id="periode_evaluasi" name="periode_evaluasi" type="date" value="{{ old('periode_evaluasi') }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                                        @error('periode_evaluasi')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    @php
                                        $numericFields = [
                                            'jumlah_keluarga' => 'Jumlah Keluarga',
                                            'jumlah_keluarga_sasaran' => 'Jumlah Keluarga Sasaran',
                                            'peringkat_1' => 'Peringkat 1',
                                            'peringkat_2' => 'Peringkat 2',
                                            'peringkat_3' => 'Peringkat 3',
                                            'peringkat_4' => 'Peringkat 4',
                                            'peringkat_lebih_4' => 'Peringkat Lebih 4',
                                            'total_berisiko' => 'Total Berisiko',
                                            'tidak_berisiko' => 'Tidak Berisiko',
                                            'sasaran_baduta' => 'Sasaran Baduta',
                                            'sasaran_balita' => 'Sasaran Balita',
                                            'sasaran_pus' => 'Sasaran PUS',
                                            'sasaran_pus_hamil' => 'Sasaran PUS Hamil',
                                            'desil_1' => 'Desil 1',
                                            'desil_2' => 'Desil 2',
                                            'desil_3' => 'Desil 3',
                                            'desil_4' => 'Desil 4',
                                            'desil_5' => 'Desil 5',
                                            'desil_6' => 'Desil 6',
                                            'desil_7' => 'Desil 7',
                                            'desil_8' => 'Desil 8',
                                            'desil_9' => 'Desil 9',
                                            'desil_10' => 'Desil 10',
                                            'air_tidak_layak' => 'Air Tidak Layak',
                                            'jamban_tidak_layak' => 'Jamban Tidak Layak',
                                            'terlalu_muda' => 'Terlalu Muda',
                                            'terlalu_tua' => 'Terlalu Tua',
                                            'terlalu_dekat' => 'Terlalu Dekat',
                                            'terlalu_banyak' => 'Terlalu Banyak',
                                            'jumlah_terlalu' => 'Jumlah Terlalu',
                                        ];
                                    @endphp

                                    @foreach ($numericFields as $field => $label)
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-slate-700" for="{{ $field }}">{{ $label }}</label>
                                            <input id="{{ $field }}" name="{{ $field }}" type="number" min="0" value="{{ old($field, 0) }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                                            @error($field)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex flex-wrap gap-3 pt-2">
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#1f4b75] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#173a5c]">
                                        {{ $editingRecord ? 'Simpan Perubahan' : 'Simpan Data Indikator' }}
                                    </button>
                                    @if ($editingRecord)
                                        <a href="{{ route('admin.import-data.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Batal</a>
                                    @endif
                                </div>
                            </form>
                        </section>

                        <section class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-200 px-6 py-4 sm:px-8">
                                <h2 class="text-lg font-semibold text-slate-900">Data yang sudah masuk</h2>
                                <p class="mt-1 text-sm text-slate-500">Daftar rekap terbaru yang tersimpan di database.</p>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                                    <thead class="bg-slate-50 text-slate-600">
                                        <tr>
                                            <th class="px-6 py-3 font-medium">Periode</th>
                                            <th class="px-6 py-3 font-medium">Wilayah</th>
                                            <th class="px-6 py-3 font-medium">Keluarga</th>
                                            <th class="px-6 py-3 font-medium">Berisiko</th>
                                            <th class="px-6 py-3 font-medium">Terlalu</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 bg-white">
                                        @forelse ($records as $record)
                                            <tr>
                                                <td class="px-6 py-4 text-slate-600">{{ $record->periode_evaluasi?->format('d-m-Y') }}</td>
                                                <td class="px-6 py-4 text-slate-600">
                                                    {{ $record->kecamatan?->nama_kecamatan ?? '-' }}
                                                    @if ($record->kelurahan)
                                                        <div class="text-xs text-slate-400">{{ $record->kelurahan->nama_kelurahan }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-slate-600">{{ $record->jumlah_keluarga }}</td>
                                                <td class="px-6 py-4 text-slate-600">{{ $record->total_berisiko }}</td>
                                                <td class="px-6 py-4 text-slate-600">
                                                    {{ $record->jumlah_terlalu }}
                                                    <div class="mt-2 flex flex-wrap gap-2">
                                                        <a href="{{ route('admin.import-data.edit', $record) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-[#1f4b75] hover:bg-slate-50">Ubah</a>
                                                        <form action="{{ route('admin.import-data.destroy', $record) }}" method="post" onsubmit="return confirm('Yakin ingin menghapus data evaluasi ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">Belum ada data indikator.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>