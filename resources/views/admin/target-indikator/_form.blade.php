@php($target = $targetIndikator ?? null)

<section class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="kode_indikator" class="mb-2 block text-sm font-medium text-slate-700">Kode dan Nama Indikator</label>
            <select id="kode_indikator" name="kode_indikator" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                <option value="">Pilih indikator</option>
                @foreach ($indikatorOptions as $code => $name)
                    <option value="{{ $code }}" @selected(old('kode_indikator', $target?->kode_indikator) === $code)>{{ $code }} &mdash; {{ $name }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs leading-5 text-slate-500">Nama indikator disimpan otomatis sesuai kode yang dipilih.</p>
            @error('kode_indikator')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="tahun_berlaku" class="mb-2 block text-sm font-medium text-slate-700">Tahun Berlaku</label>
            <input id="tahun_berlaku" name="tahun_berlaku" type="number" min="2000" max="{{ now()->year + 1 }}" step="1" required value="{{ old('tahun_berlaku', $target?->tahun_berlaku ?? ($tahunDefault ?? now()->year)) }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
            @error('tahun_berlaku')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="nilai_target" class="mb-2 block text-sm font-medium text-slate-700">Nilai Target (%)</label>
            <input id="nilai_target" name="nilai_target" type="number" min="0" max="100" step="0.0001" inputmode="decimal" required value="{{ old('nilai_target', $target?->nilai_target) }}" placeholder="Contoh: 25,0000" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
            <p class="mt-2 text-xs text-slate-500">Masukkan angka 0 sampai 100, maksimal 4 angka desimal.</p>
            @error('nilai_target')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="arah_target" class="mb-2 block text-sm font-medium text-slate-700">Arah Target</label>
            <select id="arah_target" name="arah_target" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                @foreach ($arahTargetOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('arah_target', $target?->arah_target ?? 'Minimize') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-slate-500">Seluruh KPI utama saat ini menggunakan arah Minimize.</p>
            @error('arah_target')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="jenis_target" class="mb-2 block text-sm font-medium text-slate-700">Jenis Target</label>
            <select id="jenis_target" name="jenis_target" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                @foreach ($jenisTargetOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('jenis_target', $target?->jenis_target) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('jenis_target')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="sumber_target" class="mb-2 block text-sm font-medium text-slate-700">Sumber Target <span class="font-normal text-slate-400">(opsional)</span></label>
            <input id="sumber_target" name="sumber_target" type="text" maxlength="255" value="{{ old('sumber_target', $target?->sumber_target) }}" placeholder="Dokumen atau dasar penetapan target" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
            @error('sumber_target')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
        <input type="hidden" name="status_aktif" value="0">
        <label class="flex items-start gap-3" for="status_aktif">
            <input id="status_aktif" name="status_aktif" type="checkbox" value="1" @checked((bool) old('status_aktif', $target?->status_aktif ?? true)) class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#1f4b75] focus:ring-[#1f4b75]">
            <span><span class="block text-sm font-medium text-slate-800">Aktifkan target</span><span class="mt-1 block text-xs leading-5 text-slate-500">Target aktif diprioritaskan sebagai tolak ukur evaluasi untuk kode dan tahun ini.</span></span>
        </label>
        @error('status_aktif')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</section>

<aside class="rounded-[1.5rem] border border-blue-200 bg-blue-50 p-5 text-sm leading-6 text-blue-900">Jangan memasukkan target perkiraan sebagai target resmi. Jika target aktif tidak tersedia, sistem menggunakan agregat Kabupaten Subang pada tahun yang sama sebagai tolak ukur internal.</aside>
