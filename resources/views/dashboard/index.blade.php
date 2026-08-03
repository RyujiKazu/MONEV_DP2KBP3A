@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $formatInteger = static fn (int|float|string|null $value): string => number_format((float) ($value ?? 0), 0, ',', '.');
    $formatDecimal = static fn (int|float|string|null $value): string => $value === null
        ? 'Data Tidak Tersedia'
        : number_format((float) $value, 2, ',', '.');
    $formatPercent = static fn (int|float|string|null $value): string => $value === null
        ? 'Data Tidak Tersedia'
        : number_format((float) $value, 2, ',', '.').'%';
    $formatDelta = static function (int|float|string|null $value): string {
        if ($value === null) {
            return 'Data Tidak Tersedia';
        }

        $number = (float) $value;
        $prefix = $number > 0 ? '+' : '';

        return $prefix.number_format($number, 2, ',', '.').' poin persentase';
    };

    $records = $evaluation['records'];
    $selectedRecord = $records->first();
    $isSingleKecamatan = filled($selectedKecamatan);
    $selectedKecamatanName = $isSingleKecamatan
        ? ($kecamatans->firstWhere('kode_kecamatan', $selectedKecamatan)?->nama_kecamatan ?? $selectedKecamatan)
        : 'Semua Kecamatan';
    $fourTTotal = data_get($chartData, 'four_t.jumlah_4t');
    $fourTCoverage = data_get($chartData, 'four_t.coverage', []);
    $fourTAvailableRecords = (int) data_get($fourTCoverage, 'available_records', 0);
    $fourTTotalRecords = (int) data_get($fourTCoverage, 'total_records', 0);
@endphp

@section('content')
    <div class="mx-auto max-w-[96rem] space-y-6" data-dashboard-root>
        <header class="rounded-[2rem] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_80px_rgba(15,23,42,0.10)] sm:px-8">
            <div>
                <div>
                    <p class="text-sm font-semibold tracking-[0.22em] text-[#1f4b75] uppercase">Performance Dashboard</p>
                    <h1 class="mt-3 text-3xl font-semibold text-[#1f3550] sm:text-4xl">Monitoring Keluarga Berisiko Stunting</h1>
                    <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                        Evaluasi tahun {{ $selectedYear }} dengan pembanding tahun {{ $evaluation['previous_year'] }} untuk {{ $selectedKecamatanName }}.
                    </p>
                </div>
            </div>
        </header>

        <x-flash-messages />

        <section class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="dashboard-filter-title">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 id="dashboard-filter-title" class="text-lg font-semibold text-slate-900">Filter dashboard</h2>
                    <p class="mt-1 text-sm text-slate-500">Pilih periode dan cakupan wilayah yang ingin dianalisis.</p>
                </div>

                <form method="get" action="{{ route('dashboard.index') }}" class="grid w-full gap-4 sm:grid-cols-2 lg:max-w-3xl lg:grid-cols-[minmax(9rem,0.65fr)_minmax(15rem,1.35fr)_auto]">
                    <div>
                        <label for="tahun" class="mb-2 block text-sm font-medium text-slate-700">Tahun</label>
                        <select id="tahun" name="tahun" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                            @forelse ($years as $year)
                                <option value="{{ $year }}" @selected((int) $selectedYear === (int) $year)>{{ $year }}</option>
                            @empty
                                <option value="{{ $selectedYear }}">{{ $selectedYear }}</option>
                            @endforelse
                        </select>
                    </div>

                    <div>
                        <label for="kode_kecamatan" class="mb-2 block text-sm font-medium text-slate-700">Kecamatan</label>
                        <select id="kode_kecamatan" name="kode_kecamatan" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                            <option value="">Semua Kecamatan</option>
                            @foreach ($kecamatans as $kecamatan)
                                <option value="{{ $kecamatan->kode_kecamatan }}" @selected((string) $selectedKecamatan === (string) $kecamatan->kode_kecamatan)>
                                    {{ $kecamatan->nama_kecamatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="inline-flex items-center justify-center self-end rounded-xl bg-[#1f4b75] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#173a5c] focus:outline-none focus:ring-4 focus:ring-[#b9cddd] sm:col-span-2 lg:col-span-1">
                        Terapkan
                    </button>
                </form>
            </div>
        </section>

        @if (! $evaluation['has_data'])
            <x-empty-state
                title="Data evaluasi belum tersedia"
                description="Belum ada data KRS untuk tahun dan kecamatan yang dipilih. Ubah filter atau pastikan Admin telah memasukkan rekap KRS periode tersebut."
            >
                @if (auth()->user()?->role === 'Admin' && Route::has('admin.rekap-krs.create'))
                    <a href="{{ route('admin.rekap-krs.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#1f4b75] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#173a5c] focus:outline-none focus:ring-4 focus:ring-[#b9cddd]">
                        Tambah Data KRS
                    </a>
                @endif
            </x-empty-state>
        @else
            <section aria-labelledby="ringkasan-dashboard-title">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <h2 id="ringkasan-dashboard-title" class="text-xl font-semibold text-[#1f3550]">Ringkasan kinerja</h2>
                        <p class="mt-1 text-sm text-slate-500">Angka agregat dihitung dari seluruh data dalam cakupan filter.</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Total keluarga sasaran</p>
                        <p class="mt-3 text-3xl font-semibold text-[#1f3550]">{{ $formatInteger($evaluation['summary']['jumlah_keluarga_sasaran']) }}</p>
                        <p class="mt-2 text-xs text-slate-500">Keluarga pada tahun {{ $selectedYear }}</p>
                    </article>

                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Total KRS</p>
                        <p class="mt-3 text-3xl font-semibold text-[#1f3550]">{{ $formatInteger($evaluation['summary']['total_krs']) }}</p>
                        <p class="mt-2 text-xs text-slate-500">Keluarga berisiko stunting</p>
                    </article>

                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm" title="Total KRS dibagi total keluarga sasaran, kemudian dikalikan 100.">
                        <p class="text-sm font-medium text-slate-500">Persentase KRS</p>
                        <p class="mt-3 text-3xl font-semibold text-[#1f3550]">{{ $formatPercent($evaluation['summary']['persentase_krs']) }}</p>
                        <p class="mt-2 text-xs text-slate-500">KPI-01 agregat terpilih</p>
                    </article>

                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Kecamatan prioritas tinggi</p>
                        <p class="mt-3 text-3xl font-semibold text-red-700">{{ $formatInteger($evaluation['summary']['prioritas_tinggi']) }}</p>
                        <p class="mt-2 text-xs text-slate-500">Berdasarkan skor KPI valid</p>
                    </article>
                </div>
            </section>

            <div class="space-y-6" data-dashboard-charts aria-busy="true">
                <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800" role="status" data-dashboard-loading>
                    Menyiapkan visualisasi dashboard&hellip;
                </div>
                <div class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert" data-dashboard-error>
                    Visualisasi tidak dapat dimuat. Data tabel tetap dapat digunakan.
                </div>

                <div class="grid gap-6 xl:grid-cols-2" data-dashboard-chart-content>
                    @if ($isSingleKecamatan && $selectedRecord)
                        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-medium text-slate-500">Skor Prioritas Kecamatan</p>
                            <div class="mt-5 flex flex-wrap items-end justify-between gap-4">
                                <div>
                                    <p class="text-4xl font-semibold text-[#1f3550]">{{ $formatDecimal($selectedRecord['priority']['score']) }}</p>
                                    <p class="mt-2 text-xs text-slate-500">Dari {{ $selectedRecord['priority']['valid_count'] }} KPI valid</p>
                                </div>
                                <x-status-badge :status="$selectedRecord['priority']['level']" :tone="$selectedRecord['priority']['color']" class="px-3 py-1.5" />
                            </div>
                        </article>
                    @else
                        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm" data-chart-card>
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Distribusi Tingkat Prioritas</h2>
                                <p class="mt-1 text-sm text-slate-500">Jumlah kecamatan menurut tingkat prioritas.</p>
                            </div>
                            <div class="relative mt-5 h-80" data-chart-frame>
                                <canvas id="priorityChart" role="img" aria-label="Diagram donat distribusi tingkat prioritas kecamatan"></canvas>
                                <p class="absolute inset-0 hidden items-center justify-center text-center text-sm text-slate-500" data-chart-empty>Data prioritas belum tersedia.</p>
                            </div>
                        </article>
                    @endif

                    @if ($isSingleKecamatan && $selectedRecord)
                        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-medium text-slate-500">Faktor Risiko Dominan</p>
                            <p class="mt-4 text-2xl font-semibold text-[#1f3550]">{{ $selectedRecord['dominant_factor']['label'] ?? 'Data Tidak Tersedia' }}</p>
                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                Faktor dipilih dari KPI air minum, jamban, dan PUS 4 Terlalu berdasarkan skor evaluasi serta perbandingan terhadap tolok ukur.
                            </p>
                        </article>
                    @else
                        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm" data-chart-card>
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Distribusi Faktor Dominan</h2>
                                <p class="mt-1 text-sm text-slate-500">Faktor risiko dominan pada setiap kecamatan.</p>
                            </div>
                            <div class="relative mt-5 h-80" data-chart-frame>
                                <canvas id="dominantChart" role="img" aria-label="Diagram distribusi faktor risiko dominan"></canvas>
                                <p class="absolute inset-0 hidden items-center justify-center text-center text-sm text-slate-500" data-chart-empty>Faktor dominan belum dapat dihitung.</p>
                            </div>
                        </article>
                    @endif

                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2" data-chart-card>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Perbandingan KPI Antarperiode</h2>
                            <p class="mt-1 text-sm text-slate-500">Nilai tahun {{ $evaluation['previous_year'] }} dibandingkan dengan {{ $evaluation['year'] }} dalam persen.</p>
                        </div>
                        <div class="relative mt-5 h-80" data-chart-frame>
                            <canvas id="comparisonChart" role="img" aria-label="Diagram batang perbandingan empat KPI tahun berjalan dan sebelumnya"></canvas>
                            <p class="absolute inset-0 hidden items-center justify-center text-center text-sm text-slate-500" data-chart-empty>Nilai KPI belum tersedia.</p>
                        </div>
                        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Perubahan KPI dalam poin persentase">
                            @foreach ($chartData['comparison']['labels'] as $index => $code)
                                <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                                    <p class="text-xs font-semibold text-slate-500">{{ $code }}</p>
                                    <p class="mt-2 text-sm font-semibold text-[#1f3550]">{{ $formatDelta($chartData['comparison']['delta'][$index] ?? null) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $chartData['comparison']['status'][$index] ?? 'Data Tidak Tersedia' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2" data-chart-card>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Peringkat Persentase KRS Antarkecamatan</h2>
                            <p class="mt-1 text-sm text-slate-500">Diurutkan dari KPI-01 tertinggi; warna menunjukkan tingkat prioritas.</p>
                        </div>
                        <div class="relative mt-5 min-h-80" data-chart-frame>
                            <canvas id="rankingChart" role="img" aria-label="Diagram batang horizontal persentase KRS antarkecamatan"></canvas>
                            <p class="absolute inset-0 hidden items-center justify-center text-center text-sm text-slate-500" data-chart-empty>Data peringkat belum tersedia.</p>
                        </div>
                    </article>

                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm" data-chart-card>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Faktor Lingkungan</h2>
                            <p class="mt-1 text-sm text-slate-500">KPI-02 air minum dan KPI-03 jamban tidak layak.</p>
                        </div>
                        <div class="relative mt-5 min-h-80" data-chart-frame>
                            <canvas id="environmentChart" role="img" aria-label="Diagram persentase faktor lingkungan per kecamatan"></canvas>
                            <p class="absolute inset-0 hidden items-center justify-center text-center text-sm text-slate-500" data-chart-empty>Data faktor lingkungan belum tersedia.</p>
                        </div>
                    </article>

                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm" data-chart-card>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Komposisi PUS 4 Terlalu</h2>
                            <p class="mt-1 text-sm text-slate-500">Komposisi subkategori; jumlahnya tidak digunakan untuk menghitung jumlah 4T.</p>
                        </div>
                        <div class="relative mt-5 h-80" data-chart-frame>
                            <canvas id="fourTChart" role="img" aria-label="Diagram komposisi terlalu muda, terlalu tua, terlalu dekat, dan terlalu banyak"></canvas>
                            <p class="absolute inset-0 hidden items-center justify-center text-center text-sm text-slate-500" data-chart-empty>Komposisi PUS 4 Terlalu belum tersedia.</p>
                        </div>
                        <p class="mt-3 text-xs leading-5 text-slate-500">Jumlah unik PUS 4T dari data sumber: <span class="font-semibold text-slate-700">{{ $fourTTotal === null ? 'Data Tidak Tersedia' : $formatInteger($fourTTotal) }}</span>@if($fourTTotalRecords > 0 && $fourTAvailableRecords < $fourTTotalRecords) <span class="font-medium text-amber-700">(tersedia pada {{ $fourTAvailableRecords }} dari {{ $fourTTotalRecords }} rekap)</span>@endif. Grafik tetap menampilkan subkategori mentah; satu PUS dapat memiliki lebih dari satu kondisi sehingga keempat subkategori tidak dijumlahkan untuk mengisi total atau KPI-04.</p>
                    </article>

                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2" data-chart-card>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Komposisi Peringkat Kesejahteraan</h2>
                            <p class="mt-1 text-sm text-slate-500">Batang bertumpuk per kecamatan untuk peringkat 1, 2, 3, 4, dan lebih dari 4.</p>
                        </div>
                        <div class="relative mt-5 min-h-80" data-chart-frame>
                            <canvas id="welfareChart" role="img" aria-label="Diagram batang bertumpuk komposisi peringkat kesejahteraan"></canvas>
                            <p class="absolute inset-0 hidden items-center justify-center text-center text-sm text-slate-500" data-chart-empty>Data peringkat kesejahteraan belum tersedia.</p>
                        </div>
                    </article>
                </div>
            </div>

            <section class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm" aria-labelledby="hasil-evaluasi-title">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 id="hasil-evaluasi-title" class="text-lg font-semibold text-slate-900">Hasil Evaluasi dan Rekomendasi Awal</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500">Peringkat tetap mengikuti posisi kecamatan pada cakupan kabupaten.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[112rem] divide-y divide-slate-200 text-left text-xs">
                        <caption class="sr-only">Tabel hasil evaluasi keluarga berisiko stunting per kecamatan</caption>
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-medium">Peringkat</th>
                                <th scope="col" class="px-4 py-3 font-medium">Kecamatan</th>
                                <th scope="col" class="px-4 py-3 font-medium">KPI-01 {{ $evaluation['previous_year'] }}</th>
                                <th scope="col" class="px-4 py-3 font-medium">KPI-01 {{ $evaluation['year'] }}</th>
                                <th scope="col" class="px-4 py-3 font-medium">Perubahan</th>
                                <th scope="col" class="px-4 py-3 font-medium">Status Tren</th>
                                <th scope="col" class="px-4 py-3 font-medium">Tolok Ukur</th>
                                <th scope="col" class="px-4 py-3 font-medium">Status Evaluasi</th>
                                <th scope="col" class="px-4 py-3 font-medium">Skor</th>
                                <th scope="col" class="px-4 py-3 font-medium">Tingkat Prioritas</th>
                                <th scope="col" class="px-4 py-3 font-medium">Faktor Dominan</th>
                                <th scope="col" class="px-4 py-3 font-medium">Rekomendasi Awal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($records as $item)
                                @php($kpiUtama = $item['indicators']['KPI-01'])
                                <tr class="align-top transition hover:bg-slate-50/70">
                                    <td class="px-4 py-4 font-semibold text-slate-900">{{ $item['rank'] }}</td>
                                    <th scope="row" class="px-4 py-4 font-semibold text-slate-900">{{ $item['nama_kecamatan'] }}</th>
                                    <td class="px-4 py-4 text-slate-600">{{ $formatPercent($kpiUtama['previous']) }}</td>
                                    <td class="px-4 py-4 font-semibold text-[#1f3550]">{{ $formatPercent($kpiUtama['actual']) }}</td>
                                    <td class="px-4 py-4 text-slate-600">{{ $formatDelta($kpiUtama['delta']) }}</td>
                                    <td class="px-4 py-4"><x-status-badge :status="$kpiUtama['status_tren']" /></td>
                                    <td class="px-4 py-4 text-slate-600">
                                        <span class="block font-semibold text-slate-800">{{ $formatPercent($kpiUtama['benchmark']) }}</span>
                                        <span class="mt-1 block">{{ $kpiUtama['benchmark_source'] }}</span>
                                        @if ($kpiUtama['benchmark_detail'])
                                            <span class="mt-1 block max-w-48 leading-5 text-slate-500">{{ $kpiUtama['benchmark_detail'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4"><x-status-badge :status="$kpiUtama['status']" :tone="$kpiUtama['color']" /></td>
                                    <td class="px-4 py-4 font-semibold text-slate-900">{{ $formatDecimal($item['priority']['score']) }}</td>
                                    <td class="px-4 py-4"><x-status-badge :status="$item['priority']['level']" :tone="$item['priority']['color']" /></td>
                                    <td class="px-4 py-4 text-slate-700">{{ $item['dominant_factor']['label'] ?? 'Data Tidak Tersedia' }}</td>
                                    <td class="px-4 py-4">
                                        <ul class="min-w-80 space-y-2 text-slate-600">
                                            @foreach ($item['recommendations'] as $recommendation)
                                                <li class="flex gap-2 leading-5"><span aria-hidden="true">&bull;</span><span>{{ $recommendation }}</span></li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="metodologi-title">
                <details>
                    <summary id="metodologi-title" class="cursor-pointer text-base font-semibold text-[#1f3550] focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Metodologi dan batas interpretasi</summary>
                    <div class="mt-4 grid gap-5 text-sm leading-6 text-slate-600 lg:grid-cols-2">
                        <div>
                            <p class="font-semibold text-slate-800">Rumus KPI</p>
                            <ul class="mt-2 space-y-1">
                                <li>KPI-01: total KRS / keluarga sasaran &times; 100.</li>
                                <li>KPI-02: KRS tanpa air minum layak / total KRS &times; 100.</li>
                                <li>KPI-03: KRS tanpa jamban layak / total KRS &times; 100.</li>
                                <li>KPI-04: jumlah 4T / PUS &times; 100.</li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">Keterangan</p>
                            <p class="mt-2">Agregat kabupaten memakai jumlah seluruh pembilang dibagi jumlah seluruh penyebut, bukan rata-rata persentase kecamatan. Jika target aktif tidak tersedia, agregat Kabupaten Subang menjadi tolok ukur internal.</p>
                            <p class="mt-2">Klasifikasi status merupakan aturan analitis sistem, bukan klasifikasi resmi BKKBN. Rekomendasi bersifat awal dan tidak menggantikan keputusan Kepala Bidang PKK.</p>
                        </div>
                    </div>
                </details>
            </section>
        @endif
    </div>
@endsection

@if ($evaluation['has_data'])
    @push('scripts')
        <script>
            window.__MONEV_DASHBOARD__ = {{ \Illuminate\Support\Js::from($chartData) }};
        </script>
    @endpush
@endif
