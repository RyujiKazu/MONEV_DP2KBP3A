<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <title>Laporan Evaluasi KRS {{ $filters['tahun'] }}</title>
        <style>
            @page { margin: 10mm 10mm 14mm; }
            * { box-sizing: border-box; }
            body { margin: 0; color: #1e293b; font-family: DejaVu Sans, sans-serif; font-size: 7px; line-height: 1.35; }
            h1, p { margin: 0; }
            .header { border-bottom: 2px solid #1f4b75; padding-bottom: 7px; }
            .header h1 { color: #1f3550; font-size: 15px; }
            .header p { margin-top: 3px; color: #475569; font-size: 8px; }
            .meta, .summary, .report, .indicator-report { width: 100%; border-collapse: collapse; }
            .meta { margin: 7px 0; }
            .meta td { width: 25%; padding: 2px 6px 2px 0; vertical-align: top; }
            .summary { margin-bottom: 7px; }
            .summary td { width: 25%; border: 1px solid #cbd5e1; padding: 5px; }
            .summary strong { display: block; margin-top: 2px; color: #1f3550; font-size: 11px; }
            .report { table-layout: fixed; }
            .report th, .report td { border: 1px solid #cbd5e1; padding: 3px; text-align: left; vertical-align: top; overflow-wrap: anywhere; }
            .report th { background: #e8eef3; color: #1f3550; }
            .report tr, .indicator-report tr { page-break-inside: avoid; break-inside: avoid; }
            .section-title { margin: 8px 0 4px; color: #1f3550; font-size: 10px; page-break-after: avoid; }
            .indicator-report { table-layout: fixed; }
            .indicator-report th, .indicator-report td { border: 1px solid #cbd5e1; padding: 3px; text-align: left; vertical-align: top; overflow-wrap: anywhere; }
            .indicator-report th { background: #e8eef3; color: #1f3550; }
            .rank { width: 4%; } .district { width: 8%; } .metric { width: 7%; } .trend { width: 7%; }
            .benchmark { width: 10%; } .status { width: 8%; } .factor { width: 10%; } .recommendation { width: 20%; }
            .empty { border: 1px dashed #94a3b8; padding: 18px; text-align: center; color: #64748b; }
            .note { margin-top: 7px; border: 1px solid #fcd34d; background: #fffbeb; padding: 5px; }
            .simulation-note { margin: 0 0 7px; border: 1px solid #f59e0b; background: #fffbeb; color: #78350f; padding: 5px; }
            .footer { position: fixed; right: 0; bottom: -9mm; left: 0; color: #64748b; text-align: center; font-size: 6px; }
            .page-number:after { content: counter(page); }
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

        <div class="footer">Laporan Evaluasi KRS DP2KBP3A Kabupaten Subang &mdash; Halaman <span class="page-number"></span></div>
        <header class="header"><h1>Laporan Evaluasi Keluarga Berisiko Stunting</h1><p>DP2KBP3A Kabupaten Subang &mdash; Tahun {{ $filters['tahun'] }} dibandingkan dengan {{ $evaluation['previous_year'] }}</p></header>
        <table class="meta"><tr><td><strong>Wilayah</strong><br>{{ $filters['nama_kecamatan'] ?? 'Semua Kecamatan' }}</td><td><strong>Tingkat prioritas</strong><br>{{ $filters['tingkat_prioritas'] ?? 'Semua Tingkat' }}</td><td><strong>Status tren</strong><br>{{ $filters['status_tren'] ?? 'Semua Status' }}</td><td><strong>Dibuat</strong><br>{{ $generated_at->format('d-m-Y H:i') }} oleh {{ $generated_by }}</td></tr><tr><td colspan="2"><strong>Data tahun {{ $evaluation['year'] }}: {{ $currentDataStatus }}</strong><br>Sumber: {{ $currentDataSources === [] ? 'Sumber tidak dicantumkan' : implode('; ', $currentDataSources) }}</td><td colspan="2"><strong>Data pembanding {{ $evaluation['previous_year'] }}: {{ $previousDataStatus }}</strong><br>Sumber: {{ $previousDataSources === [] ? 'Sumber tidak dicantumkan' : implode('; ', $previousDataSources) }}</td></tr></table>
        @if ($previousContainsSimulation)
            <div class="simulation-note"><strong>Perhatian:</strong> Data pembanding tahun {{ $evaluation['previous_year'] }} merupakan data simulasi sementara untuk pengujian sistem. Hasil perbandingan dan tren bukan perbandingan dua periode data aktual.</div>
        @endif
        <table class="summary"><tr><td>Keluarga sasaran<strong>{{ $number($countyTotals['jumlah_keluarga_sasaran']) }}</strong></td><td>Total KRS<strong>{{ $number($countyTotals['total_krs']) }}</strong></td><td>KPI-01 Kabupaten<strong>{{ $percent($countyKpi['actual']) }}</strong></td><td>Tren Kabupaten<strong>{{ $countyKpi['status_tren'] }}</strong></td></tr></table>

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
                        <tr><td>{{ $item['rank'] }}</td><td><strong>{{ $item['nama_kecamatan'] }}</strong></td><td><strong>{{ $dataMetadata['status'] }}</strong><br>{{ $dataMetadata['source'] }}</td><td>{{ $percent($kpi['previous']) }}</td><td>{{ $percent($kpi['actual']) }}</td><td>{{ $delta($kpi['delta']) }}</td><td>{{ $kpi['status_tren'] }}</td><td>{{ $percent($kpi['benchmark']) }}<br>{{ $kpi['benchmark_source'] }}@if($kpi['benchmark_detail'])<br>{{ $kpi['benchmark_detail'] }}@endif</td><td>{{ $kpi['status'] }}</td><td>{{ $decimal($item['priority']['score']) }}</td><td>{{ $item['priority']['level'] }}</td><td>{{ $item['dominant_factor']['label'] ?? 'Data Tidak Tersedia' }}</td><td>{{ $item['recommendation_text'] }}</td></tr>
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
                            <tr><td><strong>{{ $item['nama_kecamatan'] }}</strong></td><td><strong>{{ $dataMetadata['status'] }}</strong><br>{{ $dataMetadata['source'] }}</td><td><strong>{{ $indicator['code'] }}</strong><br>{{ $indicator['name'] }}</td><td>{{ $percent($indicator['previous']) }}</td><td>{{ $percent($indicator['actual']) }}</td><td>{{ $delta($indicator['delta']) }}</td><td>{{ $indicator['status_tren'] }}</td><td>{{ $percent($indicator['benchmark']) }}<br>{{ $indicator['benchmark_source'] }}@if($indicator['benchmark_detail'])<br>{{ $indicator['benchmark_detail'] }}@endif</td><td>{{ $indicator['status'] }}</td><td>{{ $decimal($indicator['score']) }}</td></tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif
        <div class="note"><strong>Catatan:</strong> Status evaluasi merupakan aturan analitis sistem, bukan klasifikasi resmi BKKBN. Rekomendasi bersifat awal dan tidak menggantikan keputusan Kepala Bidang PKK. Agregat kabupaten memakai jumlah seluruh pembilang dibagi jumlah seluruh penyebut.</div>
    </body>
</html>
