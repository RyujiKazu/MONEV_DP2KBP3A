<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Dashboard Executive | Sistem Monev Stunting DP2KBP3A</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    </head>
    <body class="min-h-screen bg-[#eef2f5] text-slate-900 antialiased">
        <main class="min-h-screen lg:flex">
            @include('partials.sidebar')

            <section class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_80px_rgba(15,23,42,0.10)] sm:px-8">
                        <p class="text-sm font-semibold tracking-[0.22em] text-[#1f4b75] uppercase">Executive Dashboard</p>
                        <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h1 class="text-3xl font-semibold text-[#1f3550] sm:text-4xl">Ringkasan Evaluasi KRS Kabupaten Subang</h1>
                                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                                    Pantau total keluarga sasaran, total keluarga berisiko, dan prioritas wilayah berdasarkan periode evaluasi yang dipilih.
                                </p>
                            </div>

                            <form method="get" action="{{ route('dashboard') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2 lg:min-w-[420px]">
                                <div>
                                    <label class="mb-2 block text-xs font-semibold tracking-[0.18em] text-slate-500 uppercase" for="bulan">Bulan</label>
                                    <select id="bulan" name="bulan" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                                        @foreach (range(1, 12) as $bulan)
                                            <option value="{{ $bulan }}" @selected((int) $selectedMonth === $bulan)>{{ ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$bulan - 1] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold tracking-[0.18em] text-slate-500 uppercase" for="tahun">Tahun</label>
                                    <select id="tahun" name="tahun" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                                        @foreach ($periods->pluck('tahun')->unique()->sortDesc() as $tahun)
                                            <option value="{{ $tahun }}" @selected((int) $selectedYear === (int) $tahun)>{{ $tahun }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold tracking-[0.18em] text-slate-500 uppercase" for="kode_kecamatan">Kecamatan</label>
                                    <select id="kode_kecamatan" name="kode_kecamatan" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                                        <option value="">Semua kecamatan</option>
                                        @foreach ($kecamatans as $kecamatan)
                                            <option value="{{ $kecamatan->kode_kecamatan }}" @selected(($selectedKecamatan ?? null) === $kecamatan->kode_kecamatan)>{{ $kecamatan->nama_kecamatan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold tracking-[0.18em] text-slate-500 uppercase" for="kode_kelurahan">Kelurahan</label>
                                    <select id="kode_kelurahan" name="kode_kelurahan" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                                        <option value="">Semua kelurahan</option>
                                        @foreach ($kelurahans as $kelurahan)
                                            <option value="{{ $kelurahan->kode_kelurahan }}" @selected(($selectedKelurahan ?? null) === $kelurahan->kode_kelurahan)>{{ $kelurahan->nama_kelurahan }} - {{ $kelurahan->kecamatan?->nama_kecamatan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[#1f4b75] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#173a5c]">
                                        Terapkan Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-3">
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-semibold text-[#1f3550]">Total Keluarga Sasaran</p>
                            <p class="mt-3 text-4xl font-semibold text-slate-900">{{ number_format((int) ($totals->total_keluarga_sasaran ?? 0), 0, ',', '.') }}</p>
                            <p class="mt-2 text-sm text-slate-500">{{ $scopeLabel }} | {{ $periodLabel }}</p>
                        </div>
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-semibold text-[#1f3550]">Total Berisiko</p>
                            <p class="mt-3 text-4xl font-semibold text-rose-600">{{ number_format((int) ($totals->total_berisiko ?? 0), 0, ',', '.') }}</p>
                            <p class="mt-2 text-sm text-slate-500">Akumulasi data yang tervalidasi</p>
                        </div>
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-semibold text-[#1f3550]">Total Tidak Berisiko</p>
                            <p class="mt-3 text-4xl font-semibold text-emerald-600">{{ number_format((int) ($totals->total_tidak_berisiko ?? 0), 0, ',', '.') }}</p>
                            <p class="mt-2 text-sm text-slate-500">Bagian keluarga yang aman</p>
                        </div>
                    </div>

                    <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                        <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-[#1f3550]">Traffic Light System</p>
                                    <h2 class="mt-1 text-xl font-semibold text-slate-900">Persentase keamanan wilayah</h2>
                                </div>
                                <div class="rounded-full bg-slate-50 px-4 py-2 text-sm font-semibold {{ $gaugeState['class'] }}">
                                    {{ $gaugeState['label'] }}
                                </div>
                            </div>

                            <div class="mt-8 flex items-center justify-center">
                                <div class="flex h-72 w-72 items-center justify-center rounded-full p-5 shadow-inner" style="background: conic-gradient({{ $gaugeState['bar'] }} 0deg, {{ $gaugeState['bar'] }} {{ $safePercentage * 3.6 }}deg, #e5e7eb {{ $safePercentage * 3.6 }}deg, #e5e7eb 360deg);">
                                    <div class="flex h-full w-full flex-col items-center justify-center rounded-full bg-white text-center">
                                        <p class="text-sm font-semibold tracking-[0.22em] text-slate-400 uppercase">Aman</p>
                                        <p class="mt-3 text-5xl font-semibold text-slate-900">{{ number_format($safePercentage, 1) }}%</p>
                                        <p class="mt-2 max-w-[180px] text-sm leading-6 text-slate-500">Semakin tinggi persentase, semakin besar bagian keluarga yang tidak berisiko.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl bg-emerald-50 px-4 py-4 text-center">
                                    <p class="text-xs font-semibold tracking-[0.2em] text-emerald-700 uppercase">Hijau</p>
                                    <p class="mt-2 text-sm text-emerald-700">Aman</p>
                                </div>
                                <div class="rounded-2xl bg-amber-50 px-4 py-4 text-center">
                                    <p class="text-xs font-semibold tracking-[0.2em] text-amber-700 uppercase">Kuning</p>
                                    <p class="mt-2 text-sm text-amber-700">Waspada</p>
                                </div>
                                <div class="rounded-2xl bg-rose-50 px-4 py-4 text-center">
                                    <p class="text-xs font-semibold tracking-[0.2em] text-rose-700 uppercase">Merah</p>
                                    <p class="mt-2 text-sm text-rose-700">Kritis</p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-[#1f3550]">Peringkat Kecamatan</p>
                                    <h2 class="mt-1 text-xl font-semibold text-slate-900">{{ $selectedKelurahan ? 'KPI kelurahan terpilih' : ($selectedKecamatan ? '5 kelurahan dengan KRS tertinggi' : '5 kecamatan dengan KRS tertinggi') }}</h2>
                                </div>
                                <p class="text-sm text-slate-500">Periode {{ $periodLabel }}</p>
                            </div>

                            <div class="mt-6 h-[360px]">
                                <canvas id="topWilayahChart"></canvas>
                            </div>
                        </section>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-3">
                        <div class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-semibold text-[#1f3550]">Risiko Lingkungan</p>
                            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ number_format((int) (($totals->air_tidak_layak ?? 0) + ($totals->jamban_tidak_layak ?? 0)), 0, ',', '.') }}</p>
                            <p class="mt-2 text-sm text-slate-500">Air tidak layak + jamban tidak layak</p>
                        </div>
                        <div class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-semibold text-[#1f3550]">Risiko Reproduksi</p>
                            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ number_format((int) (($totals->terlalu_muda ?? 0) + ($totals->terlalu_tua ?? 0) + ($totals->terlalu_dekat ?? 0) + ($totals->terlalu_banyak ?? 0)), 0, ',', '.') }}</p>
                            <p class="mt-2 text-sm text-slate-500">Akumulasi indikator 4 terlalu</p>
                        </div>
                        <div class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-semibold text-[#1f3550]">Data Aktual</p>
                            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ count($topKecamatan) }}</p>
                            <p class="mt-2 text-sm text-slate-500">Wilayah masuk pemantauan utama</p>
                        </div>
                    </div>

                    <section class="rounded-[2rem] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_80px_rgba(15,23,42,0.10)] sm:px-8">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold tracking-[0.22em] text-[#1f4b75] uppercase">Kesimpulan Evaluasi & Rekomendasi Tindakan</p>
                                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Ringkasan otomatis berdasarkan KPI {{ $scopeLabel }}</h2>
                            </div>
                            <div class="rounded-full {{ $overallSummary['badge'] }} px-4 py-2 text-sm font-semibold">
                                Status Terutama: {{ $overallSummary['status'] }}
                            </div>
                        </div>

                        <p class="mt-4 max-w-4xl text-sm leading-7 text-slate-600">
                            {{ $overallSummary['text'] }}
                        </p>

                        <div class="mt-6 grid gap-4 lg:grid-cols-3">
                            @foreach ($evaluationSummaries as $summary)
                                <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5">
                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="text-base font-semibold text-slate-900">{{ $summary['title'] }}</h3>
                                        <span class="rounded-full {{ $summary['badge'] }} px-3 py-1 text-xs font-semibold">{{ $summary['status'] }}</span>
                                    </div>
                                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $summary['text'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                </div>
            </section>
        </main>

        <script>
            const topWilayahLabels = @json(($selectedKelurahan ?? null) ? $topKelurahan->pluck('nama_kelurahan') : ($selectedKecamatan ? $topKelurahan->pluck('nama_kelurahan') : $topKecamatan->pluck('nama_kecamatan')));
            const topWilayahValues = @json(($selectedKelurahan ?? null) ? $topKelurahan->pluck('total_berisiko') : ($selectedKecamatan ? $topKelurahan->pluck('total_berisiko') : $topKecamatan->pluck('total_berisiko')));

            const canvas = document.getElementById('topWilayahChart');
            if (canvas) {
                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: topWilayahLabels,
                        datasets: [{
                            label: 'Total Berisiko',
                            data: topWilayahValues,
                            backgroundColor: '#1f4b75',
                            borderRadius: 10,
                            maxBarThickness: 48,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                        },
                        scales: {
                            x: {
                                ticks: {
                                    color: '#64748b',
                                },
                                grid: {
                                    display: false,
                                },
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#64748b',
                                },
                                grid: {
                                    color: '#e2e8f0',
                                },
                            },
                        },
                    },
                });
            }
        </script>
    </body>
</html>