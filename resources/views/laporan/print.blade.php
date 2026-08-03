<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laporan Evaluasi KRS {{ $filters['tahun'] }}</title>
        <style>
            @page { size: A4 landscape; margin: 12mm; }
            * { box-sizing: border-box; }
            body { margin: 0; color: #1e293b; background: #fff; font-family: Arial, sans-serif; font-size: 10px; line-height: 1.45; }
            h1, h2, p { margin: 0; }
            .toolbar { display: flex; gap: 8px; margin-bottom: 16px; }
            .button { border: 1px solid #1f4b75; border-radius: 6px; background: #1f4b75; color: #fff; cursor: pointer; padding: 8px 12px; text-decoration: none; }
            .button.secondary { background: #fff; color: #1f4b75; }
            .header { border-bottom: 2px solid #1f4b75; padding-bottom: 12px; }
            .header h1 { color: #1f3550; font-size: 20px; }
            .header p { margin-top: 4px; color: #475569; }
            .meta { width: 100%; margin: 12px 0; border-collapse: collapse; }
            .meta td { width: 25%; padding: 3px 8px 3px 0; vertical-align: top; }
            .summary { width: 100%; margin-bottom: 14px; border-collapse: separate; border-spacing: 6px; }
            .summary td { width: 25%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; }
            .summary strong { display: block; margin-top: 3px; color: #1f3550; font-size: 15px; }
            .report { width: 100%; border-collapse: collapse; table-layout: fixed; }
            .report th, .report td { border: 1px solid #cbd5e1; padding: 5px; text-align: left; vertical-align: top; overflow-wrap: anywhere; }
            .report th { background: #e8eef3; color: #1f3550; font-weight: 700; }
            .report .rank { width: 4%; } .report .district { width: 8%; } .report .metric { width: 7%; }
            .report .trend { width: 8%; } .report .benchmark { width: 10%; } .report .status { width: 8%; }
            .report .factor { width: 10%; } .report .recommendation { width: 20%; }
            .report tr, .indicator-report tr { page-break-inside: avoid; break-inside: avoid; }
            .section-title { margin: 16px 0 6px; color: #1f3550; font-size: 14px; page-break-after: avoid; }
            .indicator-report { width: 100%; border-collapse: collapse; table-layout: fixed; }
            .indicator-report th, .indicator-report td { border: 1px solid #cbd5e1; padding: 5px; text-align: left; vertical-align: top; overflow-wrap: anywhere; }
            .indicator-report th { background: #e8eef3; color: #1f3550; font-weight: 700; }
            .note { margin-top: 14px; border: 1px solid #fcd34d; background: #fffbeb; padding: 9px; }
            .simulation-note { margin: 0 0 12px; border: 1px solid #f59e0b; background: #fffbeb; color: #78350f; padding: 8px; }
            .empty { border: 1px dashed #94a3b8; padding: 24px; text-align: center; color: #64748b; }
            @media print { .no-print { display: none !important; } }
        </style>
    </head>
    <body>
        @php
            $number = static fn ($value): string => number_format((float) ($value ?? 0), 0, ',', '.');
            $percent = static fn ($value): string => $value === null ? 'Data Tidak Tersedia' : number_format((float) $value, 2, ',', '.').'%';
            $decimal = static fn ($value): string => $value === null ? 'Data Tidak Tersedia' : number_format((float) $value, 2, ',', '.');
            $delta = static fn ($value): string => $value === null ? 'Data Tidak Tersedia' : (((float) $value > 0 ? '+' : '').number_format((float) $value, 2, ',', '.').' pp');
            $countyTotals = $evaluation['county']['current']['totals'];
            $countyKpi = $evaluation['county']['indicators']['KPI-01'];
            $currentMetadata = data_get($evaluation, 'data_metadata.current', []);
            $previousMetadata = data_get($evaluation, 'data_metadata.previous', []);
            $currentDataStatus = (string) data_get($currentMetadata, 'status', 'Data Tidak Tersedia');
            $previousDataStatus = (string) data_get($previousMetadata, 'status', 'Data Tidak Tersedia');
            $currentDataSources = array_values(array_filter((array) data_get($currentMetadata, 'sources', []), static fn ($source): bool => filled($source)));
            $previousDataSources = array_values(array_filter((array) data_get($previousMetadata, 'sources', []), static fn ($source): bool => filled($source)));
            $previousContainsSimulation = in_array($previousDataStatus, ['Simulasi', 'Campuran'], true);
            $recordProvenance = static fn ($item): array => [
                'status' => (string) data_get($item, 'data_metadata.current.status', data_get($item, 'is_simulasi', false) ? 'Simulasi' : 'Aktual'),
                'source' => data_get($item, 'data_metadata.current.source') ?: data_get($item, 'sumber_data') ?: data_get($item, 'record.sumber_data') ?: 'Sumber tidak dicantumkan',
            ];
        @endphp

        <div class="toolbar no-print">
            <button type="button" class="button" onclick="window.print()">Cetak Laporan</button>
            <button type="button" class="button secondary" onclick="window.close()">Tutup</button>
        </div>

        <header class="header">
            <h1>Laporan Evaluasi Keluarga Berisiko Stunting</h1>
            <p>DP2KBP3A Kabupaten Subang &mdash; Tahun {{ $filters['tahun'] }} dibandingkan dengan {{ $evaluation['previous_year'] }}</p>
        </header>

        <table class="meta" aria-label="Identitas laporan">
            <tr><td><strong>Wilayah</strong><br>{{ $filters['nama_kecamatan'] ?? 'Semua Kecamatan' }}</td><td><strong>Tingkat prioritas</strong><br>{{ $filters['tingkat_prioritas'] ?? 'Semua Tingkat' }}</td><td><strong>Status tren</strong><br>{{ $filters['status_tren'] ?? 'Semua Status' }}</td><td><strong>Dibuat</strong><br>{{ $generated_at->format('d-m-Y H:i') }} oleh {{ $generated_by }}</td></tr>
            <tr><td colspan="2"><strong>Data tahun {{ $evaluation['year'] }}: {{ $currentDataStatus }}</strong><br>Sumber: {{ $currentDataSources === [] ? 'Sumber tidak dicantumkan' : implode('; ', $currentDataSources) }}</td><td colspan="2"><strong>Data pembanding {{ $evaluation['previous_year'] }}: {{ $previousDataStatus }}</strong><br>Sumber: {{ $previousDataSources === [] ? 'Sumber tidak dicantumkan' : implode('; ', $previousDataSources) }}</td></tr>
        </table>

        @if ($previousContainsSimulation)
            <div class="simulation-note"><strong>Perhatian:</strong> Data pembanding tahun {{ $evaluation['previous_year'] }} merupakan data simulasi sementara untuk pengujian sistem. Hasil perbandingan dan tren bukan perbandingan dua periode data aktual.</div>
        @endif

        <table class="summary" aria-label="Ringkasan Kabupaten Subang">
            <tr>
                <td>Keluarga sasaran<strong>{{ $number($countyTotals['jumlah_keluarga_sasaran']) }}</strong></td>
                <td>Total KRS<strong>{{ $number($countyTotals['total_krs']) }}</strong></td>
                <td>KPI-01 Kabupaten<strong>{{ $percent($countyKpi['actual']) }}</strong></td>
                <td>Tren Kabupaten<strong>{{ $countyKpi['status_tren'] }}</strong></td>
            </tr>
        </table>

        @if ($records->isEmpty())
            <div class="empty">Tidak ada data evaluasi yang memenuhi filter laporan.</div>
        @else
            <table class="report">
                <thead><tr><th class="rank">Peringkat</th><th class="district">Kecamatan</th><th>Status dan Sumber Data</th><th class="metric">KPI-01 {{ $evaluation['previous_year'] }}</th><th class="metric">KPI-01 {{ $evaluation['year'] }}</th><th class="metric">Perubahan</th><th class="trend">Tren</th><th class="benchmark">Tolok Ukur</th><th class="status">Status</th><th class="metric">Skor</th><th class="status">Prioritas</th><th class="factor">Faktor Dominan</th><th class="recommendation">Rekomendasi Awal</th></tr></thead>
                <tbody>
                    @foreach ($records as $item)
                        @php
                            $kpi = $item['indicators']['KPI-01'];
                            $dataMetadata = $recordProvenance($item);
                        @endphp
                        <tr>
                            <td>{{ $item['rank'] }}</td><td><strong>{{ $item['nama_kecamatan'] }}</strong></td><td><strong>{{ $dataMetadata['status'] }}</strong><br>{{ $dataMetadata['source'] }}</td><td>{{ $percent($kpi['previous']) }}</td><td>{{ $percent($kpi['actual']) }}</td><td>{{ $delta($kpi['delta']) }}</td><td>{{ $kpi['status_tren'] }}</td>
                            <td>{{ $percent($kpi['benchmark']) }}<br>{{ $kpi['benchmark_source'] }}@if($kpi['benchmark_detail'])<br>{{ $kpi['benchmark_detail'] }}@endif</td><td>{{ $kpi['status'] }}</td><td>{{ $decimal($item['priority']['score']) }}</td><td>{{ $item['priority']['level'] }}</td><td>{{ $item['dominant_factor']['label'] ?? 'Data Tidak Tersedia' }}</td><td>{{ $item['recommendation_text'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h2 class="section-title">Rincian Evaluasi per Indikator</h2>
            <table class="indicator-report">
                <thead><tr><th>Kecamatan</th><th>Status dan Sumber Data</th><th>Indikator</th><th>Tahun {{ $evaluation['previous_year'] }}</th><th>Tahun {{ $evaluation['year'] }}</th><th>Delta</th><th>Tren</th><th>Benchmark dan Sumber</th><th>Status</th><th>Skor</th></tr></thead>
                <tbody>
                    @foreach ($records as $item)
                        @php($dataMetadata = $recordProvenance($item))
                        @foreach ($item['indicators'] as $indicator)
                            <tr>
                                <td><strong>{{ $item['nama_kecamatan'] }}</strong></td>
                                <td><strong>{{ $dataMetadata['status'] }}</strong><br>{{ $dataMetadata['source'] }}</td>
                                <td><strong>{{ $indicator['code'] }}</strong><br>{{ $indicator['name'] }}</td>
                                <td>{{ $percent($indicator['previous']) }}</td>
                                <td>{{ $percent($indicator['actual']) }}</td>
                                <td>{{ $delta($indicator['delta']) }}</td>
                                <td>{{ $indicator['status_tren'] }}</td>
                                <td>{{ $percent($indicator['benchmark']) }}<br>{{ $indicator['benchmark_source'] }}@if($indicator['benchmark_detail'])<br>{{ $indicator['benchmark_detail'] }}@endif</td>
                                <td>{{ $indicator['status'] }}</td>
                                <td>{{ $decimal($indicator['score']) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="note"><strong>Catatan:</strong> Status evaluasi merupakan aturan analitis sistem, bukan klasifikasi resmi BKKBN. Rekomendasi bersifat awal dan tidak menggantikan keputusan Kepala Bidang PKK. Agregat kabupaten memakai jumlah seluruh pembilang dibagi jumlah seluruh penyebut.</div>
    </body>
</html>
