@extends('layouts.app')

@section('title', 'Laporan Evaluasi')

@php
    $formatInteger = static fn (int|float|string|null $value): string => number_format((float) ($value ?? 0), 0, ',', '.');
    $formatDecimal = static fn (int|float|string|null $value): string => $value === null ? 'Data Tidak Tersedia' : number_format((float) $value, 2, ',', '.');
    $formatPercent = static fn (int|float|string|null $value): string => $value === null ? 'Data Tidak Tersedia' : number_format((float) $value, 2, ',', '.').'%';
    $formatDelta = static function (int|float|string|null $value): string {
        if ($value === null) return 'Data Tidak Tersedia';
        $number = (float) $value;
        return ($number > 0 ? '+' : '').number_format($number, 2, ',', '.').' poin persentase';
    };
    $exportQuery = array_filter([
        'tahun' => $filters['tahun'],
        'kode_kecamatan' => $filters['kode_kecamatan'],
        'tingkat_prioritas' => $filters['tingkat_prioritas'],
        'status_tren' => $filters['status_tren'],
    ], static fn ($value): bool => $value !== null && $value !== '');
    $countyTotals = $evaluation['county']['current']['totals'];
    $countyKpiUtama = $evaluation['county']['indicators']['KPI-01'];
    $countyHighPriority = $evaluation['all_records']->where('priority.level', 'Prioritas Tinggi')->count();
    $currentMetadata = data_get($evaluation, 'data_metadata.current', []);
    $previousMetadata = data_get($evaluation, 'data_metadata.previous', []);
    $currentDataStatus = (string) data_get($currentMetadata, 'status', 'Data Tidak Tersedia');
    $previousDataStatus = (string) data_get($previousMetadata, 'status', 'Data Tidak Tersedia');
    $currentDataSources = array_values(array_filter((array) data_get($currentMetadata, 'sources', []), static fn ($source): bool => filled($source)));
    $previousDataSources = array_values(array_filter((array) data_get($previousMetadata, 'sources', []), static fn ($source): bool => filled($source)));
    $currentDataNotes = array_values(array_filter((array) data_get($currentMetadata, 'notes', []), static fn ($note): bool => filled($note)));
    $previousDataNotes = array_values(array_filter((array) data_get($previousMetadata, 'notes', []), static fn ($note): bool => filled($note)));
    $previousContainsSimulation = in_array($previousDataStatus, ['Simulasi', 'Campuran'], true);
    $recordProvenance = static fn ($item): array => [
        'status' => (string) data_get($item, 'data_metadata.current.status', data_get($item, 'is_simulasi', false) ? 'Simulasi' : 'Aktual'),
        'source' => data_get($item, 'data_metadata.current.source') ?: data_get($item, 'sumber_data') ?: data_get($item, 'record.sumber_data') ?: 'Sumber tidak dicantumkan',
        'note' => data_get($item, 'data_metadata.current.note') ?: data_get($item, 'catatan_data') ?: data_get($item, 'record.catatan_data'),
    ];
@endphp

@section('content')
    <div class="mx-auto max-w-[96rem] space-y-6">
        <header class="rounded-[2rem] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_80px_rgba(15,23,42,0.10)] sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold tracking-[0.22em] text-[#1f4b75] uppercase">Laporan</p>
                    <h1 class="mt-3 text-3xl font-semibold text-[#1f3550] sm:text-4xl">Laporan Evaluasi KRS</h1>
                    <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                        Periode {{ $filters['tahun'] }} dibandingkan dengan {{ $evaluation['previous_year'] }}{{ $filters['nama_kecamatan'] ? ' untuk Kecamatan '.$filters['nama_kecamatan'] : ' untuk seluruh Kabupaten Subang' }}.
                    </p>
                </div>
                <div class="text-sm text-slate-500">
                    <p>Dibuat: <span class="font-medium text-slate-700">{{ $generated_at->format('d-m-Y H:i') }}</span></p>
                    <p class="mt-1">Oleh: <span class="font-medium text-slate-700">{{ $generated_by }}</span></p>
                </div>
            </div>
        </header>

        <x-flash-messages />

        <section class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="provenance-laporan-title">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h2 id="provenance-laporan-title" class="text-lg font-semibold text-slate-900">Status dan sumber data</h2>
                    <p class="mt-1 text-sm text-slate-500">Metadata ini melekat pada periode yang dianalisis dan periode pembanding.</p>
                </div>
                <div class="grid flex-1 gap-3 sm:grid-cols-2 lg:max-w-4xl">
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2"><p class="text-sm font-semibold text-slate-800">Tahun {{ $evaluation['year'] }}</p><x-status-badge :status="$currentDataStatus" /></div>
                        <p class="mt-2 text-xs leading-5 text-slate-600"><span class="font-semibold">Sumber:</span> {{ $currentDataSources === [] ? 'Sumber tidak dicantumkan' : implode('; ', $currentDataSources) }}</p>
                        @if ($currentDataNotes !== [])<p class="mt-1 text-xs leading-5 text-slate-500"><span class="font-semibold">Catatan:</span> {{ implode('; ', $currentDataNotes) }}</p>@endif
                    </article>
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2"><p class="text-sm font-semibold text-slate-800">Tahun {{ $evaluation['previous_year'] }}</p><x-status-badge :status="$previousDataStatus" /></div>
                        <p class="mt-2 text-xs leading-5 text-slate-600"><span class="font-semibold">Sumber:</span> {{ $previousDataSources === [] ? 'Sumber tidak dicantumkan' : implode('; ', $previousDataSources) }}</p>
                        @if ($previousDataNotes !== [])<p class="mt-1 text-xs leading-5 text-slate-500"><span class="font-semibold">Catatan:</span> {{ implode('; ', $previousDataNotes) }}</p>@endif
                    </article>
                </div>
            </div>
            @if ($previousContainsSimulation)
                <div class="mt-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-950" role="note"><span class="font-semibold">Perhatian:</span> Data pembanding tahun {{ $evaluation['previous_year'] }} merupakan data simulasi sementara untuk pengujian sistem. Hasil perbandingan dan tren bukan perbandingan dua periode data aktual.</div>
            @endif
        </section>

        <section class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="filter-laporan-title">
            <div class="mb-5">
                <h2 id="filter-laporan-title" class="text-lg font-semibold text-slate-900">Filter laporan</h2>
                <p class="mt-1 text-sm text-slate-500">Gunakan filter yang sama untuk tampilan web dan berkas ekspor.</p>
            </div>

            <form method="get" action="{{ route('laporan.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label for="laporan_tahun" class="mb-2 block text-sm font-medium text-slate-700">Tahun</label>
                    <select id="laporan_tahun" name="tahun" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                        @forelse ($years as $year)
                            <option value="{{ $year }}" @selected((int) $filters['tahun'] === (int) $year)>{{ $year }}</option>
                        @empty
                            <option value="{{ $filters['tahun'] }}">{{ $filters['tahun'] }}</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label for="laporan_kecamatan" class="mb-2 block text-sm font-medium text-slate-700">Kecamatan</label>
                    <select id="laporan_kecamatan" name="kode_kecamatan" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                        <option value="">Semua Kecamatan</option>
                        @foreach ($kecamatans as $kecamatan)
                            <option value="{{ $kecamatan->kode_kecamatan }}" @selected((string) $filters['kode_kecamatan'] === (string) $kecamatan->kode_kecamatan)>{{ $kecamatan->nama_kecamatan }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tingkat_prioritas" class="mb-2 block text-sm font-medium text-slate-700">Tingkat Prioritas</label>
                    <select id="tingkat_prioritas" name="tingkat_prioritas" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                        <option value="">Semua Tingkat</option>
                        @foreach (['Prioritas Rendah', 'Prioritas Sedang', 'Prioritas Tinggi'] as $level)
                            <option value="{{ $level }}" @selected($filters['tingkat_prioritas'] === $level)>{{ $level }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status_tren" class="mb-2 block text-sm font-medium text-slate-700">Status Tren KPI-01</label>
                    <select id="status_tren" name="status_tren" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                        <option value="">Semua Status</option>
                        @foreach (['Membaik', 'Tetap', 'Memburuk', 'Data Pembanding Belum Tersedia', 'Data Tidak Tersedia'] as $trend)
                            <option value="{{ $trend }}" @selected($filters['status_tren'] === $trend)>{{ $trend }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-4">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#1f4b75] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#173a5c] focus:outline-none focus:ring-4 focus:ring-[#b9cddd]">Terapkan Filter</button>
                    <a href="{{ route('laporan.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Reset</a>
                    <span class="hidden flex-1 sm:block"></span>
                    <a href="{{ route('laporan.print', $exportQuery) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-xl border border-[#1f4b75] px-4 py-3 text-sm font-medium text-[#1f4b75] transition hover:bg-[#eef4f7] focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Tampilan Cetak</a>
                    <a href="{{ route('laporan.csv', $exportQuery) }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-300 px-4 py-3 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50 focus:outline-none focus:ring-4 focus:ring-emerald-100">Unduh CSV</a>
                    <a href="{{ route('laporan.pdf', $exportQuery) }}" class="inline-flex items-center justify-center rounded-xl border border-red-300 px-4 py-3 text-sm font-medium text-red-700 transition hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-100">Unduh PDF</a>
                </div>
            </form>
        </section>

        <section aria-labelledby="ringkasan-kabupaten-title">
            <div class="mb-4">
                <h2 id="ringkasan-kabupaten-title" class="text-xl font-semibold text-[#1f3550]">Ringkasan Kabupaten Subang</h2>
                <p class="mt-1 text-sm text-slate-500">Ringkasan kabupaten tetap menggunakan seluruh kecamatan pada tahun {{ $filters['tahun'] }}.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Keluarga sasaran</p><p class="mt-3 text-2xl font-semibold text-[#1f3550]">{{ $formatInteger($countyTotals['jumlah_keluarga_sasaran']) }}</p></article>
                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Total KRS</p><p class="mt-3 text-2xl font-semibold text-[#1f3550]">{{ $formatInteger($countyTotals['total_krs']) }}</p></article>
                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">KPI-01 Kabupaten</p><p class="mt-3 text-2xl font-semibold text-[#1f3550]">{{ $formatPercent($countyKpiUtama['actual']) }}</p><p class="mt-2 text-xs text-slate-500">Tren: {{ $countyKpiUtama['status_tren'] }}</p></article>
                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Prioritas tinggi</p><p class="mt-3 text-2xl font-semibold text-red-700">{{ $formatInteger($countyHighPriority) }}</p><p class="mt-2 text-xs text-slate-500">Kecamatan</p></article>
            </div>
        </section>

        @if ($records->isEmpty())
            <x-empty-state title="Hasil laporan tidak ditemukan" description="Tidak ada data evaluasi yang memenuhi kombinasi filter saat ini. Ubah atau reset filter untuk melihat data lain." />
        @else
            <section class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm" aria-labelledby="tabel-laporan-title">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 id="tabel-laporan-title" class="text-lg font-semibold text-slate-900">Tabel Evaluasi Kecamatan</h2>
                    <p class="mt-1 text-sm text-slate-500">Menampilkan {{ $records->count() }} kecamatan sesuai filter.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[126rem] divide-y divide-slate-200 text-left text-xs">
                        <caption class="sr-only">Laporan evaluasi KRS per kecamatan</caption>
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-medium">Peringkat</th><th scope="col" class="px-4 py-3 font-medium">Kecamatan</th><th scope="col" class="px-4 py-3 font-medium">Status dan Sumber Data</th>
                                <th scope="col" class="px-4 py-3 font-medium">KPI-01 {{ $evaluation['previous_year'] }}</th><th scope="col" class="px-4 py-3 font-medium">KPI-01 {{ $evaluation['year'] }}</th>
                                <th scope="col" class="px-4 py-3 font-medium">Perubahan</th><th scope="col" class="px-4 py-3 font-medium">Tren</th>
                                <th scope="col" class="px-4 py-3 font-medium">Tolok Ukur</th><th scope="col" class="px-4 py-3 font-medium">Status</th>
                                <th scope="col" class="px-4 py-3 font-medium">Skor</th><th scope="col" class="px-4 py-3 font-medium">Prioritas</th>
                                <th scope="col" class="px-4 py-3 font-medium">Faktor Dominan</th><th scope="col" class="px-4 py-3 font-medium">Rekomendasi Awal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($records as $item)
                                @php
                                    $kpi = $item['indicators']['KPI-01'];
                                    $dataMetadata = $recordProvenance($item);
                                @endphp
                                <tr class="align-top hover:bg-slate-50/70">
                                    <td class="px-4 py-4 font-semibold">{{ $item['rank'] }}</td><th scope="row" class="px-4 py-4 font-semibold text-slate-900">{{ $item['nama_kecamatan'] }}</th>
                                    <td class="px-4 py-4"><x-status-badge :status="$dataMetadata['status']" /><span class="mt-2 block max-w-64 leading-5 text-slate-500">{{ $dataMetadata['source'] }}</span>@if($dataMetadata['note'])<span class="mt-1 block max-w-64 leading-5 text-slate-400">{{ $dataMetadata['note'] }}</span>@endif</td>
                                    <td class="px-4 py-4 text-slate-600">{{ $formatPercent($kpi['previous']) }}</td><td class="px-4 py-4 font-semibold text-[#1f3550]">{{ $formatPercent($kpi['actual']) }}</td>
                                    <td class="px-4 py-4 text-slate-600">{{ $formatDelta($kpi['delta']) }}</td><td class="px-4 py-4"><x-status-badge :status="$kpi['status_tren']" /></td>
                                    <td class="px-4 py-4 text-slate-600"><span class="block font-semibold text-slate-800">{{ $formatPercent($kpi['benchmark']) }}</span><span class="mt-1 block">{{ $kpi['benchmark_source'] }}</span>@if($kpi['benchmark_detail'])<span class="mt-1 block max-w-48 leading-5 text-slate-500">{{ $kpi['benchmark_detail'] }}</span>@endif</td>
                                    <td class="px-4 py-4"><x-status-badge :status="$kpi['status']" :tone="$kpi['color']" /></td>
                                    <td class="px-4 py-4 font-semibold">{{ $formatDecimal($item['priority']['score']) }}</td><td class="px-4 py-4"><x-status-badge :status="$item['priority']['level']" :tone="$item['priority']['color']" /></td>
                                    <td class="px-4 py-4 text-slate-700">{{ $item['dominant_factor']['label'] ?? 'Data Tidak Tersedia' }}</td>
                                    <td class="px-4 py-4"><p class="min-w-80 leading-5 text-slate-600">{{ $item['recommendation_text'] }}</p></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm" aria-labelledby="rincian-indikator-title">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 id="rincian-indikator-title" class="text-lg font-semibold text-slate-900">Rincian Evaluasi per Indikator</h2>
                    <p class="mt-1 text-sm text-slate-500">Setiap kecamatan ditampilkan dalam empat baris KPI agar hasil evaluasi dapat ditelusuri per indikator.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[102rem] divide-y divide-slate-200 text-left text-xs">
                        <caption class="sr-only">Rincian evaluasi setiap KPI untuk setiap kecamatan</caption>
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-medium">Kecamatan</th>
                                <th scope="col" class="px-4 py-3 font-medium">Status dan Sumber Data</th>
                                <th scope="col" class="px-4 py-3 font-medium">Indikator</th>
                                <th scope="col" class="px-4 py-3 font-medium">Tahun {{ $evaluation['previous_year'] }}</th>
                                <th scope="col" class="px-4 py-3 font-medium">Tahun {{ $evaluation['year'] }}</th>
                                <th scope="col" class="px-4 py-3 font-medium">Delta</th>
                                <th scope="col" class="px-4 py-3 font-medium">Tren</th>
                                <th scope="col" class="px-4 py-3 font-medium">Benchmark dan Sumber</th>
                                <th scope="col" class="px-4 py-3 font-medium">Status</th>
                                <th scope="col" class="px-4 py-3 font-medium">Skor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($records as $item)
                                @php($dataMetadata = $recordProvenance($item))
                                @foreach ($item['indicators'] as $indicator)
                                    <tr class="break-inside-avoid align-top hover:bg-slate-50/70">
                                        <th scope="row" class="px-4 py-4 font-semibold text-slate-900">{{ $item['nama_kecamatan'] }}</th>
                                        <td class="px-4 py-4"><x-status-badge :status="$dataMetadata['status']" /><span class="mt-2 block max-w-56 leading-5 text-slate-500">{{ $dataMetadata['source'] }}</span></td>
                                        <td class="px-4 py-4 text-slate-700"><span class="block font-semibold text-[#1f3550]">{{ $indicator['code'] }}</span><span class="mt-1 block max-w-64 leading-5 text-slate-500">{{ $indicator['name'] }}</span></td>
                                        <td class="px-4 py-4 text-slate-600">{{ $formatPercent($indicator['previous']) }}</td>
                                        <td class="px-4 py-4 font-semibold text-[#1f3550]">{{ $formatPercent($indicator['actual']) }}</td>
                                        <td class="px-4 py-4 text-slate-600">{{ $formatDelta($indicator['delta']) }}</td>
                                        <td class="px-4 py-4"><x-status-badge :status="$indicator['status_tren']" /></td>
                                        <td class="px-4 py-4 text-slate-600"><span class="block font-semibold text-slate-800">{{ $formatPercent($indicator['benchmark']) }}</span><span class="mt-1 block">{{ $indicator['benchmark_source'] }}</span>@if($indicator['benchmark_detail'])<span class="mt-1 block max-w-56 leading-5 text-slate-500">{{ $indicator['benchmark_detail'] }}</span>@endif</td>
                                        <td class="px-4 py-4"><x-status-badge :status="$indicator['status']" :tone="$indicator['color']" /></td>
                                        <td class="px-4 py-4 font-semibold text-slate-900">{{ $formatDecimal($indicator['score']) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <aside class="rounded-[1.5rem] border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-900">
            <p class="font-semibold">Catatan interpretasi</p>
            <p class="mt-1">Status evaluasi merupakan aturan analitis sistem, bukan klasifikasi resmi BKKBN. Rekomendasi adalah rekomendasi awal dan tidak menggantikan keputusan Kepala Bidang PKK. Nilai agregat kabupaten dihitung dari jumlah pembilang dibagi jumlah penyebut.</p>
        </aside>
    </div>
@endsection
