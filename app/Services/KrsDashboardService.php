<?php

namespace App\Services;

use Illuminate\Support\Collection;

class KrsDashboardService
{
    /** @param array<string, mixed> $evaluation */
    public function chartData(array $evaluation): array
    {
        /** @var Collection<int, array<string, mixed>> $records */
        $records = $evaluation['records'];
        /** @var Collection<int, array<string, mixed>> $allRecords */
        $allRecords = $evaluation['all_records'];
        $focusIndicators = $evaluation['selected_kecamatan'] !== null && $records->isNotEmpty()
            ? $records->first()['indicators']
            : $evaluation['county']['indicators'];

        $priorityLabels = ['Prioritas Rendah', 'Prioritas Sedang', 'Prioritas Tinggi'];
        $priorityValues = collect($priorityLabels)
            ->map(fn (string $label): int => $records->where('priority.level', $label)->count())
            ->all();

        $dominantDistribution = $records
            ->map(fn (array $item): string => $item['dominant_factor']['label'] ?? 'Data Tidak Tersedia')
            ->countBy()
            ->sortDesc();

        return [
            'data_metadata' => $evaluation['data_metadata'] ?? [],
            'priority' => [
                'labels' => $priorityLabels,
                'values' => $priorityValues,
                'colors' => ['#16a34a', '#d97706', '#dc2626'],
            ],
            'dominant' => [
                'labels' => $dominantDistribution->keys()->values()->all(),
                'values' => $dominantDistribution->values()->all(),
            ],
            'comparison' => [
                'labels' => array_keys(KrsEvaluationService::INDICATORS),
                'current' => collect($focusIndicators)->pluck('actual')->values()->all(),
                'previous' => collect($focusIndicators)->pluck('previous')->values()->all(),
                'delta' => collect($focusIndicators)->pluck('delta')->values()->all(),
                'status' => collect($focusIndicators)->pluck('status_tren')->values()->all(),
                'year' => $evaluation['year'],
                'previous_year' => $evaluation['previous_year'],
            ],
            'ranking' => [
                'labels' => $allRecords->pluck('nama_kecamatan')->all(),
                'values' => $allRecords->map(fn (array $item): ?float => $item['indicators']['KPI-01']['actual'])->all(),
                'colors' => $allRecords->map(fn (array $item): string => $this->priorityColor($item['priority']['level']))->all(),
            ],
            'environment' => [
                'labels' => $records->pluck('nama_kecamatan')->all(),
                'air_minum' => $records->map(fn (array $item): ?float => $item['indicators']['KPI-02']['actual'])->all(),
                'jamban' => $records->map(fn (array $item): ?float => $item['indicators']['KPI-03']['actual'])->all(),
            ],
            'four_t' => [
                'labels' => ['Terlalu muda', 'Terlalu tua', 'Terlalu dekat', 'Terlalu banyak'],
                'values' => collect(['terlalu_muda', 'terlalu_tua', 'terlalu_dekat', 'terlalu_banyak'])
                    ->map(fn (string $field): int => $evaluation['selected_aggregate']['totals'][$field])
                    ->all(),
                'jumlah_4t' => $evaluation['selected_aggregate']['totals']['jumlah_4t'],
                'coverage' => $evaluation['selected_aggregate']['coverage']['KPI-04'] ?? null,
            ],
            'welfare' => [
                'labels' => $records->pluck('nama_kecamatan')->all(),
                'datasets' => collect([
                    'Peringkat 1' => 'kesejahteraan_1',
                    'Peringkat 2' => 'kesejahteraan_2',
                    'Peringkat 3' => 'kesejahteraan_3',
                    'Peringkat 4' => 'kesejahteraan_4',
                    'Lebih dari 4' => 'kesejahteraan_lebih_4',
                ])->map(fn (string $field, string $label): array => [
                    'label' => $label,
                    'values' => $records->map(fn (array $item): int => (int) $item['record']->getAttribute($field))->all(),
                ])->values()->all(),
            ],
        ];
    }

    private function priorityColor(string $level): string
    {
        return match ($level) {
            'Prioritas Tinggi' => '#dc2626',
            'Prioritas Sedang' => '#d97706',
            'Prioritas Rendah' => '#16a34a',
            default => '#94a3b8',
        };
    }
}
