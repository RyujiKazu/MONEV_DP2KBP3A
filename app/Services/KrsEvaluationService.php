<?php

namespace App\Services;

use App\Models\RekapKrs;
use App\Models\TargetIndikator;
use Illuminate\Support\Collection;

class KrsEvaluationService
{
    /** @var array<string, array{name: string, numerator: string, denominator: string}> */
    public const INDICATORS = [
        'KPI-01' => [
            'name' => 'Persentase Keluarga Berisiko Stunting',
            'numerator' => 'total_krs',
            'denominator' => 'jumlah_keluarga_sasaran',
        ],
        'KPI-02' => [
            'name' => 'Persentase KRS Tanpa Air Minum Layak',
            'numerator' => 'air_minum_tidak_layak',
            'denominator' => 'total_krs',
        ],
        'KPI-03' => [
            'name' => 'Persentase KRS Tanpa Jamban Layak',
            'numerator' => 'jamban_tidak_layak',
            'denominator' => 'total_krs',
        ],
        'KPI-04' => [
            'name' => 'Persentase PUS 4 Terlalu',
            'numerator' => 'jumlah_4t',
            'denominator' => 'pus',
        ],
    ];

    private const AGGREGATE_FIELDS = [
        'jumlah_keluarga',
        'jumlah_keluarga_sasaran',
        'kesejahteraan_1',
        'kesejahteraan_2',
        'kesejahteraan_3',
        'kesejahteraan_4',
        'kesejahteraan_lebih_4',
        'total_krs',
        'tidak_berisiko',
        'baduta',
        'balita',
        'pus',
        'pus_hamil',
        'air_minum_tidak_layak',
        'jamban_tidak_layak',
        'terlalu_muda',
        'terlalu_tua',
        'terlalu_dekat',
        'terlalu_banyak',
        'jumlah_4t',
    ];

    public function __construct(private readonly KrsRecommendationService $recommendationService) {}

    public function percentage(int|float|null $numerator, int|float|null $denominator): ?float
    {
        if ($numerator === null || $denominator === null || $denominator == 0) {
            return null;
        }

        return ($numerator / $denominator) * 100;
    }

    /**
     * @param  RekapKrs|array<string, mixed>  $record
     * @return array<string, array<string, int|float|string|null>>
     */
    public function calculateKpis(RekapKrs|array $record): array
    {
        $result = [];

        foreach (self::INDICATORS as $code => $definition) {
            $numerator = $definition['numerator'] === 'jumlah_4t'
                ? $this->nullableIntegerValue($record, $definition['numerator'])
                : $this->value($record, $definition['numerator']);
            $denominator = $this->value($record, $definition['denominator']);
            $actual = $this->percentage($numerator, $denominator);

            $result[$code] = [
                'code' => $code,
                'name' => $definition['name'],
                'numerator' => $numerator,
                'denominator' => $denominator,
                'actual' => $actual,
                'label' => $actual === null ? 'Data Tidak Tersedia' : number_format($actual, 2, ',', '.').'%',
            ];
        }

        return $result;
    }

    /**
     * Menghasilkan agregat tertimbang melalui jumlah pembilang dibagi jumlah penyebut.
     *
     * Untuk KPI-04, PUS hanya dijumlahkan dari record yang memiliki jumlah_4t.
     * Subkategori 4T tetap dijumlahkan untuk seluruh record karena digunakan oleh grafik komposisi.
     *
     * @param  iterable<RekapKrs|array<string, mixed>>  $records
     * @return array{
     *     totals: array<string, int|null>,
     *     kpis: array<string, array<string, int|float|string|null>>,
     *     coverage: array{'KPI-04': array{available_records: int, total_records: int, denominator: int}}
     * }
     */
    public function aggregate(iterable $records): array
    {
        $totals = array_fill_keys(self::AGGREGATE_FIELDS, 0);
        $totalRecords = 0;
        $availableFourTRecords = 0;
        $fourTDenominator = 0;

        foreach ($records as $record) {
            $totalRecords++;

            foreach (self::AGGREGATE_FIELDS as $field) {
                if ($field === 'jumlah_4t') {
                    continue;
                }

                $totals[$field] += $this->value($record, $field);
            }

            $jumlah4t = $this->nullableIntegerValue($record, 'jumlah_4t');

            if ($jumlah4t !== null) {
                $totals['jumlah_4t'] += $jumlah4t;
                $fourTDenominator += $this->value($record, 'pus');
                $availableFourTRecords++;
            }
        }

        if ($availableFourTRecords === 0) {
            $totals['jumlah_4t'] = null;
        }

        $kpiTotals = $totals;
        $kpiTotals['pus'] = $fourTDenominator;

        return [
            'totals' => $totals,
            'kpis' => $this->calculateKpis($kpiTotals),
            'coverage' => [
                'KPI-04' => [
                    'available_records' => $availableFourTRecords,
                    'total_records' => $totalRecords,
                    'denominator' => $fourTDenominator,
                ],
            ],
        ];
    }

    /** @return array{delta: ?float, status: string} */
    public function trend(?float $actual, ?float $previous): array
    {
        if ($actual === null) {
            return ['delta' => null, 'status' => 'Data Tidak Tersedia'];
        }

        if ($previous === null) {
            return ['delta' => null, 'status' => 'Data Pembanding Belum Tersedia'];
        }

        $delta = $actual - $previous;

        if (abs($delta) < 0.000000001) {
            return ['delta' => 0.0, 'status' => 'Tetap'];
        }

        return [
            'delta' => $delta,
            'status' => $delta < 0 ? 'Membaik' : 'Memburuk',
        ];
    }

    /**
     * @param  array{value: ?float, source: string, detail: ?string, direction: string}  $benchmark
     * @return array<string, mixed>
     */
    public function evaluateIndicator(
        string $code,
        ?float $actual,
        ?float $previous,
        array $benchmark,
    ): array {
        $trend = $this->trend($actual, $previous);
        $benchmarkValue = $benchmark['value'];
        $meets = null;

        if ($actual !== null && $benchmarkValue !== null) {
            $meets = $benchmark['direction'] === 'Maximize'
                ? $actual >= $benchmarkValue
                : $actual <= $benchmarkValue;
        }

        [$status, $color, $score] = $this->evaluationStatus($actual, $meets, $trend['status']);

        return [
            'code' => $code,
            'name' => self::INDICATORS[$code]['name'],
            'actual' => $actual,
            'previous' => $previous,
            'delta' => $trend['delta'],
            'status_tren' => $trend['status'],
            'benchmark' => $benchmarkValue,
            'benchmark_source' => $benchmark['source'],
            'benchmark_detail' => $benchmark['detail'],
            'direction' => $benchmark['direction'],
            'difference' => $actual !== null && $benchmarkValue !== null ? $actual - $benchmarkValue : null,
            'meets_benchmark' => $meets,
            'status' => $status,
            'color' => $color,
            'score' => $score,
        ];
    }

    /** @return array{0: string, 1: string, 2: ?int} */
    public function evaluationStatus(?float $actual, ?bool $meets, string $trendStatus): array
    {
        if ($actual === null || $meets === null) {
            return ['Data Tidak Tersedia', 'Abu-abu', null];
        }

        if ($trendStatus === 'Memburuk') {
            return $meets
                ? ['Perlu Perhatian', 'Kuning', 2]
                : ['Prioritas', 'Merah', 3];
        }

        return $meets
            ? ['Terkendali', 'Hijau', 1]
            : ['Perlu Perhatian', 'Kuning', 2];
    }

    /**
     * @param  array<string, array<string, mixed>>  $indicators
     * @return array{score: ?float, level: string, color: string, valid_count: int}
     */
    public function priority(array $indicators): array
    {
        $scores = collect($indicators)
            ->pluck('score')
            ->filter(fn ($score): bool => $score !== null)
            ->map(fn ($score): float => (float) $score)
            ->values();

        if ($scores->isEmpty()) {
            return [
                'score' => null,
                'level' => 'Data Tidak Tersedia',
                'color' => 'Abu-abu',
                'valid_count' => 0,
            ];
        }

        $score = $scores->average();

        if ($score < 1.67) {
            [$level, $color] = ['Prioritas Rendah', 'Hijau'];
        } elseif ($score < 2.34) {
            [$level, $color] = ['Prioritas Sedang', 'Kuning'];
        } else {
            [$level, $color] = ['Prioritas Tinggi', 'Merah'];
        }

        return [
            'score' => $score,
            'level' => $level,
            'color' => $color,
            'valid_count' => $scores->count(),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $indicators
     * @param  RekapKrs|array<string, mixed>  $record
     * @return array<string, mixed>|null
     */
    public function dominantFactor(array $indicators, RekapKrs|array $record): ?array
    {
        $definitions = [
            'KPI-02' => ['label' => 'Air minum tidak layak', 'recommendation_key' => 'air_minum'],
            'KPI-03' => ['label' => 'Jamban tidak layak', 'recommendation_key' => 'jamban'],
            'KPI-04' => ['label' => 'PUS 4 Terlalu', 'recommendation_key' => 'pus_4t'],
        ];
        $candidates = [];

        foreach ($definitions as $code => $definition) {
            $indicator = $indicators[$code] ?? null;

            if (
                ! is_array($indicator)
                || $indicator['actual'] === null
                || $indicator['actual'] <= 0
                || $indicator['score'] === null
            ) {
                continue;
            }

            $benchmark = $indicator['benchmark'];
            $dominanceValue = $benchmark !== null && $benchmark > 0
                ? $indicator['actual'] / $benchmark
                : $indicator['actual'];

            $candidates[] = array_merge($definition, [
                'code' => $code,
                'actual' => $indicator['actual'],
                'benchmark' => $benchmark,
                'score' => $indicator['score'],
                'dominance_value' => $dominanceValue,
            ]);
        }

        if ($candidates === []) {
            return null;
        }

        usort($candidates, fn (array $a, array $b): int => [$b['score'], $b['dominance_value']] <=> [$a['score'], $a['dominance_value']]);
        $dominant = $candidates[0];

        if ($dominant['code'] === 'KPI-04' && $dominant['actual'] > 0) {
            $subcategories = [
                'terlalu_muda' => 'Terlalu muda',
                'terlalu_tua' => 'Terlalu tua',
                'terlalu_dekat' => 'Terlalu dekat',
                'terlalu_banyak' => 'Terlalu banyak',
            ];
            $largestKey = collect($subcategories)
                ->keys()
                ->sortByDesc(fn (string $key): int => $this->value($record, $key))
                ->first();

            if ($largestKey !== null && $this->value($record, $largestKey) > 0) {
                $dominant['subcategory'] = $subcategories[$largestKey];
                $dominant['label'] = 'PUS 4 Terlalu — '.$subcategories[$largestKey];
                $dominant['recommendation_key'] = $largestKey;
            }
        }

        return $dominant;
    }

    /**
     * @return array<string, mixed>
     */
    public function evaluateYear(int $year, ?string $kodeKecamatan = null): array
    {
        $previousYear = $year - 1;
        $currentAll = RekapKrs::query()
            ->with('kecamatan')
            ->where('tahun', $year)
            ->get();
        $previousAll = RekapKrs::query()
            ->where('tahun', $previousYear)
            ->get()
            ->keyBy('kode_kecamatan');
        $targets = TargetIndikator::query()
            ->where('tahun_berlaku', $year)
            ->where('status_aktif', true)
            ->get()
            ->keyBy('kode_indikator');

        $countyCurrent = $this->aggregate($currentAll);
        $countyPrevious = $this->aggregate($previousAll);
        $benchmarks = $this->benchmarks($targets, $countyCurrent['kpis']);

        $evaluations = $currentAll
            ->map(fn (RekapKrs $record): array => $this->evaluateRecord(
                $record,
                $previousAll->get($record->kode_kecamatan),
                $benchmarks,
            ))
            ->all();
        $ranked = $this->rank($evaluations);
        $selected = collect($ranked)
            ->when($kodeKecamatan !== null, fn (Collection $items): Collection => $items->where('kode_kecamatan', $kodeKecamatan))
            ->values();
        $selectedRecords = $currentAll
            ->when($kodeKecamatan !== null, fn (Collection $items): Collection => $items->where('kode_kecamatan', $kodeKecamatan))
            ->values();
        $selectedPreviousRecords = $previousAll
            ->when($kodeKecamatan !== null, fn (Collection $items): Collection => $items->where('kode_kecamatan', $kodeKecamatan))
            ->values();
        $selectedAggregate = $this->aggregate($selectedRecords);
        $selectedCurrentMetadata = $this->dataMetadata($selectedRecords, $year);
        $selectedPreviousMetadata = $this->dataMetadata($selectedPreviousRecords, $previousYear);
        $countyCurrentMetadata = $this->dataMetadata($currentAll, $year);
        $countyPreviousMetadata = $this->dataMetadata($previousAll, $previousYear);

        $countyIndicators = [];

        foreach (array_keys(self::INDICATORS) as $code) {
            $countyIndicators[$code] = $this->evaluateIndicator(
                $code,
                $countyCurrent['kpis'][$code]['actual'],
                $countyPrevious['kpis'][$code]['actual'],
                $benchmarks[$code],
            );
        }

        $countyPriority = $this->priority($countyIndicators);
        $countyDominant = $this->dominantFactor($countyIndicators, $countyCurrent['totals']);

        return [
            'year' => $year,
            'previous_year' => $previousYear,
            'has_data' => $selected->isNotEmpty(),
            'selected_kecamatan' => $kodeKecamatan,
            'records' => $selected,
            'all_records' => collect($ranked),
            'summary' => [
                'jumlah_keluarga_sasaran' => $selectedAggregate['totals']['jumlah_keluarga_sasaran'],
                'total_krs' => $selectedAggregate['totals']['total_krs'],
                'persentase_krs' => $selectedAggregate['kpis']['KPI-01']['actual'],
                'prioritas_tinggi' => $selected->where('priority.level', 'Prioritas Tinggi')->count(),
            ],
            'data_metadata' => [
                'current' => $selectedCurrentMetadata,
                'previous' => $selectedPreviousMetadata,
                'county_current' => $countyCurrentMetadata,
                'county_previous' => $countyPreviousMetadata,
            ],
            'selected_aggregate' => $selectedAggregate,
            'county' => [
                'current' => $countyCurrent,
                'previous' => $countyPrevious,
                'data_metadata' => [
                    'current' => $countyCurrentMetadata,
                    'previous' => $countyPreviousMetadata,
                ],
                'indicators' => $countyIndicators,
                'priority' => $countyPriority,
                'dominant_factor' => $countyDominant,
            ],
            'benchmarks' => $benchmarks,
        ];
    }

    /** @return list<int> */
    public function availableYears(): array
    {
        return RekapKrs::query()
            ->select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->map(fn ($year): int => (int) $year)
            ->all();
    }

    public function latestYear(): int
    {
        return (int) (RekapKrs::query()->max('tahun') ?? now()->year);
    }

    /**
     * @param  Collection<string, TargetIndikator>  $targets
     * @param  array<string, array<string, mixed>>  $countyKpis
     * @return array<string, array{value: ?float, source: string, detail: ?string, direction: string}>
     */
    private function benchmarks(Collection $targets, array $countyKpis): array
    {
        $benchmarks = [];

        foreach (array_keys(self::INDICATORS) as $code) {
            $target = $targets->get($code);

            if ($target instanceof TargetIndikator) {
                $benchmarks[$code] = [
                    'value' => (float) $target->nilai_target,
                    'source' => 'Target '.$target->jenis_target,
                    'detail' => $target->sumber_target,
                    'direction' => $target->arah_target,
                ];
            } else {
                $benchmarks[$code] = [
                    'value' => $countyKpis[$code]['actual'],
                    'source' => 'Agregat Kabupaten',
                    'detail' => 'Tolok ukur internal berdasarkan agregat Kabupaten Subang pada tahun terpilih.',
                    'direction' => 'Minimize',
                ];
            }
        }

        return $benchmarks;
    }

    /**
     * @param  array<string, array{value: ?float, source: string, detail: ?string, direction: string}>  $benchmarks
     * @return array<string, mixed>
     */
    private function evaluateRecord(RekapKrs $record, ?RekapKrs $previous, array $benchmarks): array
    {
        $currentKpis = $this->calculateKpis($record);
        $previousKpis = $previous === null ? [] : $this->calculateKpis($previous);
        $indicators = [];

        foreach (array_keys(self::INDICATORS) as $code) {
            $indicators[$code] = $this->evaluateIndicator(
                $code,
                $currentKpis[$code]['actual'],
                $previousKpis[$code]['actual'] ?? null,
                $benchmarks[$code],
            );
        }

        $priority = $this->priority($indicators);
        $dominant = $this->dominantFactor($indicators, $record);
        $recommendations = $this->recommendationService->forEvaluation($indicators, $dominant, $record);
        $currentMetadata = $this->recordDataMetadata($record, (int) $record->tahun);
        $previousMetadata = $this->recordDataMetadata($previous, (int) $record->tahun - 1);

        return [
            'record' => $record,
            'kode_kecamatan' => $record->kode_kecamatan,
            'nama_kecamatan' => $record->kecamatan?->nama_kecamatan ?? $record->kode_kecamatan,
            'indicators' => $indicators,
            'priority' => $priority,
            'dominant_factor' => $dominant,
            'recommendations' => $recommendations,
            'recommendation_text' => implode(' ', $recommendations),
            'is_simulasi' => $currentMetadata['is_simulasi'],
            'sumber_data' => $currentMetadata['source'],
            'catatan_data' => $currentMetadata['note'],
            'data_metadata' => [
                'current' => $currentMetadata,
                'previous' => $previousMetadata,
            ],
            'rank' => null,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $evaluations
     * @return list<array<string, mixed>>
     */
    public function rank(array $evaluations): array
    {
        usort($evaluations, function (array $a, array $b): int {
            $priority = ($b['priority']['score'] ?? -INF) <=> ($a['priority']['score'] ?? -INF);

            if ($priority !== 0) {
                return $priority;
            }

            $kpi = ($b['indicators']['KPI-01']['actual'] ?? -INF) <=> ($a['indicators']['KPI-01']['actual'] ?? -INF);

            if ($kpi !== 0) {
                return $kpi;
            }

            $delta = ($b['indicators']['KPI-01']['delta'] ?? -INF) <=> ($a['indicators']['KPI-01']['delta'] ?? -INF);

            if ($delta !== 0) {
                return $delta;
            }

            return strcasecmp((string) $a['nama_kecamatan'], (string) $b['nama_kecamatan']);
        });

        foreach ($evaluations as $index => &$evaluation) {
            $evaluation['rank'] = $index + 1;
        }
        unset($evaluation);

        return $evaluations;
    }

    /**
     * @param  iterable<RekapKrs|array<string, mixed>>  $records
     * @return array{
     *     year: int,
     *     status: string,
     *     is_simulasi: ?bool,
     *     source: ?string,
     *     sources: list<string>,
     *     note: ?string,
     *     notes: list<string>,
     *     record_count: int,
     *     simulation_count: int
     * }
     */
    private function dataMetadata(iterable $records, int $year): array
    {
        $items = collect($records)->values();
        $recordCount = $items->count();
        $simulationCount = $items
            ->filter(fn (RekapKrs|array $record): bool => (bool) $this->rawValue($record, 'is_simulasi'))
            ->count();

        $status = match (true) {
            $recordCount === 0 => 'Data Tidak Tersedia',
            $simulationCount === 0 => 'Aktual',
            $simulationCount === $recordCount => 'Simulasi',
            default => 'Campuran',
        };
        $sources = $this->metadataValues($items, 'sumber_data');
        $notes = $this->metadataValues($items, 'catatan_data');

        return [
            'year' => $year,
            'status' => $status,
            'is_simulasi' => match ($status) {
                'Aktual' => false,
                'Simulasi' => true,
                default => null,
            },
            'source' => $sources === [] ? null : implode('; ', $sources),
            'sources' => $sources,
            'note' => $notes === [] ? null : implode('; ', $notes),
            'notes' => $notes,
            'record_count' => $recordCount,
            'simulation_count' => $simulationCount,
        ];
    }

    /**
     * @return array{
     *     year: int,
     *     status: string,
     *     is_simulasi: ?bool,
     *     source: ?string,
     *     sources: list<string>,
     *     note: ?string,
     *     notes: list<string>
     * }
     */
    private function recordDataMetadata(?RekapKrs $record, int $year): array
    {
        if ($record === null) {
            return [
                'year' => $year,
                'status' => 'Data Tidak Tersedia',
                'is_simulasi' => null,
                'source' => null,
                'sources' => [],
                'note' => null,
                'notes' => [],
            ];
        }

        $source = $this->normalizedMetadataValue($record->sumber_data);
        $note = $this->normalizedMetadataValue($record->catatan_data);

        return [
            'year' => $year,
            'status' => $record->is_simulasi ? 'Simulasi' : 'Aktual',
            'is_simulasi' => (bool) $record->is_simulasi,
            'source' => $source,
            'sources' => $source === null ? [] : [$source],
            'note' => $note,
            'notes' => $note === null ? [] : [$note],
        ];
    }

    /**
     * @param  Collection<int, RekapKrs|array<string, mixed>>  $records
     * @return list<string>
     */
    private function metadataValues(Collection $records, string $key): array
    {
        return $records
            ->map(fn (RekapKrs|array $record): ?string => $this->normalizedMetadataValue($this->rawValue($record, $key)))
            ->filter(fn (?string $value): bool => $value !== null)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizedMetadataValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }

    /** @param RekapKrs|array<string, mixed> $record */
    private function value(RekapKrs|array $record, string $key): int
    {
        return (int) ($this->rawValue($record, $key) ?? 0);
    }

    /** @param RekapKrs|array<string, mixed> $record */
    private function nullableIntegerValue(RekapKrs|array $record, string $key): ?int
    {
        $value = $this->rawValue($record, $key);

        return $value === null || $value === '' ? null : (int) $value;
    }

    /** @param RekapKrs|array<string, mixed> $record */
    private function rawValue(RekapKrs|array $record, string $key): mixed
    {
        return $record instanceof RekapKrs ? $record->getAttribute($key) : ($record[$key] ?? null);
    }
}
