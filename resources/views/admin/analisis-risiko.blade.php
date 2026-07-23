<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Analisis Risiko | Sistem Monev Stunting DP2KBP3A</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    </head>
    <body class="min-h-screen bg-[#eef2f5] text-slate-900 antialiased">
        <main class="min-h-screen lg:flex">
            @include('partials.sidebar')

            <section class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_80px_rgba(15,23,42,0.10)] sm:px-8">
                        <p class="text-sm font-semibold tracking-[0.22em] text-[#1f4b75] uppercase">Menu Analisis</p>
                        <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h1 class="text-3xl font-semibold text-[#1f3550] sm:text-4xl">Analisis Risiko</h1>
                                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                                    Halaman ini memecah indikator KRS agar pimpinan dapat melihat akar masalah berdasarkan periode yang dipilih.
                                </p>
                            </div>

                            <form method="get" action="{{ route('analisis-risiko.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2 lg:min-w-[420px]">
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
                        </div>
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-semibold text-[#1f3550]">Total Berisiko</p>
                            <p class="mt-3 text-4xl font-semibold text-rose-600">{{ number_format((int) ($totals->total_berisiko ?? 0), 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-semibold text-[#1f3550]">Persentase Aman</p>
                            <p class="mt-3 text-4xl font-semibold text-emerald-600">{{ number_format($safePercentage, 1) }}%</p>
                        </div>
                    </div>

                    <div class="grid gap-6 xl:grid-cols-3">
                        <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <div>
                                <p class="text-sm font-semibold text-[#1f3550]">Risiko Lingkungan</p>
                                <h2 class="mt-1 text-xl font-semibold text-slate-900">Air dan jamban tidak layak</h2>
                            </div>
                            <div class="mt-6 h-80">
                                <canvas id="environmentChart"></canvas>
                            </div>
                        </section>

                        <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <div>
                                <p class="text-sm font-semibold text-[#1f3550]">Risiko Kesejahteraan</p>
                                <h2 class="mt-1 text-xl font-semibold text-slate-900">Rasio desil keluarga sasaran</h2>
                            </div>

                            @if ($welfareAvailable)
                                <div class="mt-6 h-80">
                                    <canvas id="welfareChart"></canvas>
                                </div>
                            @else
                                <div class="mt-6 rounded-[1.25rem] border border-dashed border-slate-300 bg-slate-50 p-6 text-sm leading-7 text-slate-600">
                                    Belum ada data desil pada periode ini.
                                </div>
                            @endif
                        </section>

                        <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <div>
                                <p class="text-sm font-semibold text-[#1f3550]">Risiko Reproduksi</p>
                                <h2 class="mt-1 text-xl font-semibold text-slate-900">4 Terlalu</h2>
                            </div>
                            <div class="mt-6 h-80">
                                <canvas id="reproductionChart"></canvas>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        </main>

        <script>
            const environmentLabels = @json($environmentLabels);
            const environmentValues = @json($environmentValues);
            const reproductionLabels = @json($reproductionLabels);
            const reproductionValues = @json($reproductionValues);
            const welfareLabels = @json($welfareLabels);
            const welfareValues = @json($welfareValues);

            const environmentChart = document.getElementById('environmentChart');
            if (environmentChart) {
                new Chart(environmentChart, {
                    type: 'pie',
                    data: {
                        labels: environmentLabels,
                        datasets: [{
                            data: environmentValues,
                            backgroundColor: ['#f59e0b', '#1f4b75'],
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                        },
                    },
                });
            }

            const reproductionChart = document.getElementById('reproductionChart');
            if (reproductionChart) {
                new Chart(reproductionChart, {
                    type: 'bar',
                    data: {
                        labels: reproductionLabels,
                        datasets: [{
                            label: 'Jumlah Kasus',
                            data: reproductionValues,
                            backgroundColor: '#1f4b75',
                            borderRadius: 10,
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

            const welfareChart = document.getElementById('welfareChart');
            if (welfareChart) {
                new Chart(welfareChart, {
                    type: 'doughnut',
                    data: {
                        labels: welfareLabels,
                        datasets: [{
                            data: welfareValues,
                            backgroundColor: ['#1f4b75', '#2d6ea3', '#4c84b6', '#6a9bc7', '#8bb0d6', '#f59e0b', '#fbbf24', '#fcd34d', '#fde68a', '#d97706'],
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                        },
                    },
                });
            }
        </script>
    </body>
</html>