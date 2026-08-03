<?php

namespace App\Services;

use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Support\Collection;

class KrsReportService
{
    public function __construct(private readonly KrsEvaluationService $evaluationService) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function prepare(array $filters, User $user): array
    {
        $year = isset($filters['tahun']) ? (int) $filters['tahun'] : $this->evaluationService->latestYear();
        $kodeKecamatan = $filters['kode_kecamatan'] ?? null;
        $evaluation = $this->evaluationService->evaluateYear($year, $kodeKecamatan);

        /** @var Collection<int, array<string, mixed>> $records */
        $records = $evaluation['records'];

        if (! empty($filters['tingkat_prioritas'])) {
            $records = $records->where('priority.level', $filters['tingkat_prioritas'])->values();
        }

        if (! empty($filters['status_tren'])) {
            $records = $records
                ->filter(fn (array $item): bool => $item['indicators']['KPI-01']['status_tren'] === $filters['status_tren'])
                ->values();
        }

        $kecamatan = $kodeKecamatan === null ? null : Kecamatan::query()->find($kodeKecamatan);
        $evaluation['records'] = $records;
        $evaluation['has_data'] = $records->isNotEmpty();

        return [
            'evaluation' => $evaluation,
            'records' => $records,
            'data_metadata' => $evaluation['data_metadata'] ?? [],
            'filters' => [
                'tahun' => $year,
                'kode_kecamatan' => $kodeKecamatan,
                'nama_kecamatan' => $kecamatan?->nama_kecamatan,
                'tingkat_prioritas' => $filters['tingkat_prioritas'] ?? null,
                'status_tren' => $filters['status_tren'] ?? null,
            ],
            'generated_at' => now(),
            'generated_by' => $user->nama_lengkap,
        ];
    }

    public function filename(string $extension, int $year): string
    {
        return sprintf('laporan-evaluasi-krs-%d-%s.%s', $year, now()->format('Ymd'), $extension);
    }

    /**
     * @param  array<string, mixed>  $report
     * @return iterable<list<string|int|float|null>>
     */
    public function csvRows(array $report): iterable
    {
        $currentMetadata = $report['evaluation']['data_metadata']['current'] ?? [];
        $previousMetadata = $report['evaluation']['data_metadata']['previous'] ?? [];

        yield ['Laporan Evaluasi Keluarga Berisiko Stunting'];
        yield ['Tanggal generate', $report['generated_at']->format('d-m-Y H:i').' WIB'];
        yield ['Dibuat oleh', $report['generated_by']];
        yield ['Tahun', $report['filters']['tahun']];
        yield ['Tahun pembanding', $report['evaluation']['previous_year']];
        yield ['Status data tahun berjalan', $currentMetadata['status'] ?? 'Data Tidak Tersedia'];
        yield ['Sumber data tahun berjalan', $this->csvMetadataValue($currentMetadata['source'] ?? null)];
        yield ['Catatan data tahun berjalan', $this->csvMetadataValue($currentMetadata['note'] ?? null)];
        yield ['Status data tahun pembanding', $previousMetadata['status'] ?? 'Data Tidak Tersedia'];
        yield ['Sumber data tahun pembanding', $this->csvMetadataValue($previousMetadata['source'] ?? null)];
        yield ['Catatan data tahun pembanding', $this->csvMetadataValue($previousMetadata['note'] ?? null)];
        yield ['Kecamatan', $report['filters']['nama_kecamatan'] ?? 'Semua Kecamatan'];
        yield ['Tingkat prioritas', $report['filters']['tingkat_prioritas'] ?? 'Semua Tingkat Prioritas'];
        yield ['Status tren', $report['filters']['status_tren'] ?? 'Semua Status Tren'];
        yield [];
        yield ['Ringkasan Kabupaten Subang'];
        yield ['Total keluarga sasaran', $report['evaluation']['county']['current']['totals']['jumlah_keluarga_sasaran']];
        yield ['Total KRS', $report['evaluation']['county']['current']['totals']['total_krs']];
        yield ['Indikator', 'Tahun sebelumnya', 'Tahun berjalan', 'Perubahan (Poin Persentase)', 'Status tren', 'Tolok ukur', 'Sumber tolok ukur'];

        foreach ($report['evaluation']['county']['indicators'] as $indicator) {
            yield [
                $indicator['code'],
                $this->csvNumber($indicator['previous']),
                $this->csvNumber($indicator['actual']),
                $this->csvNumber($indicator['delta']),
                $indicator['status_tren'],
                $this->csvNumber($indicator['benchmark']),
                $indicator['benchmark_source'],
            ];
        }

        yield [];
        yield ['Evaluasi Kecamatan'];
        yield [
            'Peringkat',
            'Kecamatan',
            'Status Data Tahun Sebelumnya',
            'Sumber Data Tahun Sebelumnya',
            'Catatan Data Tahun Sebelumnya',
            'Status Data Tahun Berjalan',
            'Sumber Data Tahun Berjalan',
            'Catatan Data Tahun Berjalan',
            'KPI-01 Tahun Sebelumnya',
            'KPI-01 Tahun Berjalan',
            'Perubahan (Poin Persentase)',
            'Status Tren',
            'Tolok Ukur',
            'Sumber Tolok Ukur',
            'Status Evaluasi',
            'Skor Prioritas',
            'Tingkat Prioritas',
            'Faktor Dominan',
            'Rekomendasi Awal',
        ];

        foreach ($report['records'] as $item) {
            $kpi = $item['indicators']['KPI-01'];
            $itemCurrentMetadata = $item['data_metadata']['current'] ?? [];
            $itemPreviousMetadata = $item['data_metadata']['previous'] ?? [];

            yield [
                $item['rank'],
                $item['nama_kecamatan'],
                $itemPreviousMetadata['status'] ?? 'Data Tidak Tersedia',
                $this->csvMetadataValue($itemPreviousMetadata['source'] ?? null),
                $this->csvMetadataValue($itemPreviousMetadata['note'] ?? null),
                $itemCurrentMetadata['status'] ?? 'Data Tidak Tersedia',
                $this->csvMetadataValue($itemCurrentMetadata['source'] ?? null),
                $this->csvMetadataValue($itemCurrentMetadata['note'] ?? null),
                $this->csvNumber($kpi['previous']),
                $this->csvNumber($kpi['actual']),
                $this->csvNumber($kpi['delta']),
                $kpi['status_tren'],
                $this->csvNumber($kpi['benchmark']),
                $kpi['benchmark_source'],
                $kpi['status'],
                $this->csvNumber($item['priority']['score']),
                $item['priority']['level'],
                $item['dominant_factor']['label'] ?? 'Data Tidak Tersedia',
                $item['recommendation_text'],
            ];
        }

        yield [];
        yield ['Rincian Evaluasi per Indikator'];
        yield [
            'Peringkat',
            'Kecamatan',
            'Status Data Tahun Sebelumnya',
            'Sumber Data Tahun Sebelumnya',
            'Catatan Data Tahun Sebelumnya',
            'Status Data Tahun Berjalan',
            'Sumber Data Tahun Berjalan',
            'Catatan Data Tahun Berjalan',
            'Kode Indikator',
            'Nama Indikator',
            'Tahun Sebelumnya',
            'Tahun Berjalan',
            'Perubahan (Poin Persentase)',
            'Status Tren',
            'Tolok Ukur',
            'Sumber Tolok Ukur',
            'Selisih Aktual - Tolok Ukur',
            'Status Evaluasi',
            'Skor Indikator',
        ];

        foreach ($report['records'] as $item) {
            $itemCurrentMetadata = $item['data_metadata']['current'] ?? [];
            $itemPreviousMetadata = $item['data_metadata']['previous'] ?? [];

            foreach ($item['indicators'] as $indicator) {
                yield [
                    $item['rank'],
                    $item['nama_kecamatan'],
                    $itemPreviousMetadata['status'] ?? 'Data Tidak Tersedia',
                    $this->csvMetadataValue($itemPreviousMetadata['source'] ?? null),
                    $this->csvMetadataValue($itemPreviousMetadata['note'] ?? null),
                    $itemCurrentMetadata['status'] ?? 'Data Tidak Tersedia',
                    $this->csvMetadataValue($itemCurrentMetadata['source'] ?? null),
                    $this->csvMetadataValue($itemCurrentMetadata['note'] ?? null),
                    $indicator['code'],
                    $indicator['name'],
                    $this->csvNumber($indicator['previous']),
                    $this->csvNumber($indicator['actual']),
                    $this->csvNumber($indicator['delta']),
                    $indicator['status_tren'],
                    $this->csvNumber($indicator['benchmark']),
                    $indicator['benchmark_source'],
                    $this->csvNumber($indicator['difference']),
                    $indicator['status'],
                    $indicator['score'],
                ];
            }
        }
    }

    private function csvNumber(?float $number): string
    {
        return $number === null ? 'Data Tidak Tersedia' : number_format($number, 2, ',', '');
    }

    private function csvMetadataValue(mixed $value): string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : 'Tidak dicantumkan';
    }
}
