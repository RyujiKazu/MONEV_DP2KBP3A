@php
    $record = $rekapKrs ?? null;
    $numericSections = [
        [
            'code' => 'B',
            'title' => 'Data Keluarga',
            'description' => 'Jumlah keluarga, sasaran, dan hasil identifikasi risiko.',
            'fields' => [
                ['name' => 'jumlah_keluarga', 'label' => 'Jumlah Keluarga'],
                ['name' => 'jumlah_keluarga_sasaran', 'label' => 'Jumlah Keluarga Sasaran'],
                ['name' => 'total_krs', 'label' => 'Total Keluarga Berisiko Stunting'],
                ['name' => 'tidak_berisiko', 'label' => 'Keluarga Tidak Berisiko'],
            ],
        ],
        [
            'code' => 'C',
            'title' => 'Peringkat Kesejahteraan',
            'description' => 'Jumlah kelima peringkat harus sama dengan total KRS.',
            'fields' => [
                ['name' => 'kesejahteraan_1', 'label' => 'Peringkat Kesejahteraan 1'],
                ['name' => 'kesejahteraan_2', 'label' => 'Peringkat Kesejahteraan 2'],
                ['name' => 'kesejahteraan_3', 'label' => 'Peringkat Kesejahteraan 3'],
                ['name' => 'kesejahteraan_4', 'label' => 'Peringkat Kesejahteraan 4'],
                ['name' => 'kesejahteraan_lebih_4', 'label' => 'Peringkat Kesejahteraan Lebih dari 4'],
            ],
        ],
        [
            'code' => 'D',
            'title' => 'Sasaran Keluarga',
            'description' => 'Kategori dapat saling beririsan dan tidak dijumlahkan sebagai total sasaran.',
            'fields' => [
                ['name' => 'baduta', 'label' => 'Baduta'],
                ['name' => 'balita', 'label' => 'Balita'],
                ['name' => 'pus', 'label' => 'Pasangan Usia Subur (PUS)'],
                ['name' => 'pus_hamil', 'label' => 'PUS Hamil'],
            ],
        ],
        [
            'code' => 'E',
            'title' => 'Faktor Lingkungan',
            'description' => 'Jumlah keluarga berisiko dengan akses lingkungan tidak layak.',
            'fields' => [
                ['name' => 'air_minum_tidak_layak', 'label' => 'Air Minum Tidak Layak'],
                ['name' => 'jamban_tidak_layak', 'label' => 'Jamban Tidak Layak'],
            ],
        ],
        [
            'code' => 'F',
            'title' => 'PUS 4 Terlalu',
            'description' => 'Empat subkategori tetap dapat dicatat, tetapi tidak boleh dijumlahkan untuk membentuk total PUS 4T karena satu PUS dapat berada pada lebih dari satu kondisi.',
            'fields' => [
                ['name' => 'terlalu_muda', 'label' => 'Terlalu Muda'],
                ['name' => 'terlalu_tua', 'label' => 'Terlalu Tua'],
                ['name' => 'terlalu_dekat', 'label' => 'Terlalu Dekat'],
                ['name' => 'terlalu_banyak', 'label' => 'Terlalu Banyak'],
                ['name' => 'jumlah_4t', 'label' => 'Jumlah Unik PUS 4 Terlalu', 'nullable' => true],
            ],
        ],
    ];
@endphp

<section class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="bagian-a-title">
    <div class="flex gap-4">
        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#eef4f7] text-sm font-bold text-[#1f4b75]">A</span>
        <div><h2 id="bagian-a-title" class="text-lg font-semibold text-slate-900">Wilayah dan Periode</h2><p class="mt-1 text-sm text-slate-500">Satu kecamatan hanya dapat memiliki satu rekap pada tahun yang sama.</p></div>
    </div>
    <div class="mt-6 grid gap-5 md:grid-cols-2">
        <div>
            <label for="kode_kecamatan" class="mb-2 block text-sm font-medium text-slate-700">Kecamatan</label>
            <select id="kode_kecamatan" name="kode_kecamatan" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                <option value="">Pilih kecamatan</option>
                @foreach ($kecamatans as $kecamatan)
                    <option value="{{ $kecamatan->kode_kecamatan }}" @selected((string) old('kode_kecamatan', $record?->kode_kecamatan) === (string) $kecamatan->kode_kecamatan)>{{ $kecamatan->nama_kecamatan }}</option>
                @endforeach
            </select>
            @error('kode_kecamatan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="tahun" class="mb-2 block text-sm font-medium text-slate-700">Tahun</label>
            <input id="tahun" name="tahun" type="number" min="2000" max="{{ now()->year + 1 }}" step="1" required value="{{ old('tahun', $record?->tahun ?? ($tahunDefault ?? now()->year)) }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
            @error('tahun')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
            <input type="hidden" name="is_simulasi" value="0">
            <label for="is_simulasi" class="flex cursor-pointer items-start gap-3">
                <input id="is_simulasi" name="is_simulasi" type="checkbox" value="1" @checked((bool) old('is_simulasi', $record?->is_simulasi ?? false)) class="mt-0.5 h-5 w-5 rounded border-slate-300 text-[#1f4b75] focus:ring-[#b9cddd]">
                <span>
                    <span class="block text-sm font-semibold text-slate-800">Tandai sebagai data simulasi</span>
                    <span class="mt-1 block text-xs leading-5 text-slate-500">Aktifkan hanya untuk data sementara atau data uji yang bukan data aktual/resmi.</span>
                </span>
            </label>
            @error('is_simulasi')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="sumber_data" class="mb-2 block text-sm font-medium text-slate-700">Sumber Data</label>
            <input id="sumber_data" name="sumber_data" type="text" maxlength="255" value="{{ old('sumber_data', $record?->sumber_data) }}" placeholder="Nama dokumen atau sumber data" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
            @error('sumber_data')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="catatan_data" class="mb-2 block text-sm font-medium text-slate-700">Catatan Data</label>
            <textarea id="catatan_data" name="catatan_data" rows="3" placeholder="Keterangan asal, penggunaan, atau keterbatasan data" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">{{ old('catatan_data', $record?->catatan_data) }}</textarea>
            @error('catatan_data')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</section>

@foreach ($numericSections as $section)
    <section class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="bagian-{{ strtolower($section['code']) }}-title">
        <div class="flex gap-4">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#eef4f7] text-sm font-bold text-[#1f4b75]">{{ $section['code'] }}</span>
            <div><h2 id="bagian-{{ strtolower($section['code']) }}-title" class="text-lg font-semibold text-slate-900">{{ $section['title'] }}</h2><p class="mt-1 text-sm leading-6 text-slate-500">{{ $section['description'] }}</p></div>
        </div>
        <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($section['fields'] as $field)
                @php
                    $isNullable = (bool) ($field['nullable'] ?? false);
                    $fieldValue = old(
                        $field['name'],
                        $record !== null ? $record->getAttribute($field['name']) : ($isNullable ? null : 0),
                    );
                @endphp
                <div>
                    <label for="{{ $field['name'] }}" class="mb-2 block text-sm font-medium text-slate-700">{{ $field['label'] }}@if($isNullable)<span class="font-normal text-slate-400"> (opsional)</span>@endif</label>
                    <input id="{{ $field['name'] }}" name="{{ $field['name'] }}" type="number" min="0" step="1" inputmode="numeric" @if(! $isNullable) required @endif value="{{ $fieldValue }}" @if($isNullable) placeholder="Kosongkan jika tidak tersedia" aria-describedby="jumlah-4t-help" @endif class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                    @if ($isNullable)
                        <p id="jumlah-4t-help" class="mt-1 text-xs leading-5 text-slate-500">Biarkan kosong bila total unik tidak tercantum pada sumber. KPI-04 akan ditampilkan sebagai Data Tidak Tersedia.</p>
                    @endif
                    @error($field['name'])<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>
    </section>
@endforeach

<aside class="rounded-[1.5rem] border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-900">
    <p class="font-semibold">Periksa dokumen sumber sebelum menyimpan</p>
    <p class="mt-1">Total KRS ditambah keluarga tidak berisiko harus sama dengan keluarga sasaran. Sistem menolak angka yang tidak konsisten dan tidak akan mengubah nilai secara otomatis.</p>
</aside>
