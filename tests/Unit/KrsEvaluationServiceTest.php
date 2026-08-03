<?php

namespace Tests\Unit;

use App\Services\KrsEvaluationService;
use App\Services\KrsRecommendationService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class KrsEvaluationServiceTest extends TestCase
{
    private KrsEvaluationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new KrsEvaluationService(new KrsRecommendationService);
    }

    public function test_kpi_01_uses_total_krs_over_target_families(): void
    {
        $kpis = $this->service->calculateKpis($this->record());

        $this->assertEqualsWithDelta(25.0, $kpis['KPI-01']['actual'], 0.000001);
        $this->assertSame(50, $kpis['KPI-01']['numerator']);
        $this->assertSame(200, $kpis['KPI-01']['denominator']);
    }

    public function test_kpi_02_uses_unsafe_drinking_water_over_total_krs(): void
    {
        $kpis = $this->service->calculateKpis($this->record());

        $this->assertEqualsWithDelta(20.0, $kpis['KPI-02']['actual'], 0.000001);
        $this->assertSame(10, $kpis['KPI-02']['numerator']);
        $this->assertSame(50, $kpis['KPI-02']['denominator']);
    }

    public function test_kpi_03_uses_inadequate_latrines_over_total_krs(): void
    {
        $kpis = $this->service->calculateKpis($this->record());

        $this->assertEqualsWithDelta(10.0, $kpis['KPI-03']['actual'], 0.000001);
        $this->assertSame(5, $kpis['KPI-03']['numerator']);
        $this->assertSame(50, $kpis['KPI-03']['denominator']);
    }

    public function test_kpi_04_uses_total_four_t_over_pus(): void
    {
        $kpis = $this->service->calculateKpis($this->record());

        $this->assertEqualsWithDelta(20.0, $kpis['KPI-04']['actual'], 0.000001);
        $this->assertSame(8, $kpis['KPI-04']['numerator']);
        $this->assertSame(40, $kpis['KPI-04']['denominator']);
    }

    public function test_zero_denominator_returns_null_and_not_zero_percent(): void
    {
        $kpis = $this->service->calculateKpis([
            'jumlah_keluarga_sasaran' => 0,
            'total_krs' => 0,
            'air_minum_tidak_layak' => 0,
            'jamban_tidak_layak' => 0,
            'pus' => 0,
            'jumlah_4t' => 0,
        ]);

        foreach (array_keys(KrsEvaluationService::INDICATORS) as $code) {
            $this->assertNull($kpis[$code]['actual']);
            $this->assertSame('Data Tidak Tersedia', $kpis[$code]['label']);
        }

        $this->assertNull($this->service->percentage(12, 0));
    }

    public function test_county_aggregate_uses_summed_numerator_over_summed_denominator(): void
    {
        $aggregate = $this->service->aggregate([
            ['total_krs' => 1, 'jumlah_keluarga_sasaran' => 2],
            ['total_krs' => 9, 'jumlah_keluarga_sasaran' => 90],
        ]);

        $expectedWeightedPercentage = (10 / 92) * 100;

        $this->assertSame(10, $aggregate['totals']['total_krs']);
        $this->assertSame(92, $aggregate['totals']['jumlah_keluarga_sasaran']);
        $this->assertEqualsWithDelta($expectedWeightedPercentage, $aggregate['kpis']['KPI-01']['actual'], 0.000001);
        $this->assertNotEquals(30.0, round((float) $aggregate['kpis']['KPI-01']['actual'], 1));
    }

    public function test_negative_delta_is_improving(): void
    {
        $trend = $this->service->trend(12.5, 15.0);

        $this->assertEqualsWithDelta(-2.5, $trend['delta'], 0.000001);
        $this->assertSame('Membaik', $trend['status']);
    }

    public function test_zero_delta_is_stable(): void
    {
        $trend = $this->service->trend(12.5, 12.5);

        $this->assertSame(0.0, $trend['delta']);
        $this->assertSame('Tetap', $trend['status']);
    }

    public function test_positive_delta_is_worsening(): void
    {
        $trend = $this->service->trend(16.75, 12.5);

        $this->assertEqualsWithDelta(4.25, $trend['delta'], 0.000001);
        $this->assertSame('Memburuk', $trend['status']);
    }

    public function test_met_benchmark_with_improving_or_stable_trend_is_controlled(): void
    {
        $this->assertSame(
            ['Terkendali', 'Hijau', 1],
            $this->service->evaluationStatus(10.0, true, 'Membaik')
        );
        $this->assertSame(
            ['Terkendali', 'Hijau', 1],
            $this->service->evaluationStatus(10.0, true, 'Tetap')
        );
    }

    #[DataProvider('attentionStatusProvider')]
    public function test_attention_status_covers_unmet_benchmark_or_worsening_trend(bool $meets, string $trend): void
    {
        $this->assertSame(
            ['Perlu Perhatian', 'Kuning', 2],
            $this->service->evaluationStatus(18.0, $meets, $trend)
        );
    }

    /** @return array<string, array{bool, string}> */
    public static function attentionStatusProvider(): array
    {
        return [
            'belum memenuhi tetapi membaik' => [false, 'Membaik'],
            'belum memenuhi dan tetap' => [false, 'Tetap'],
            'memenuhi tetapi memburuk' => [true, 'Memburuk'],
        ];
    }

    public function test_unmet_benchmark_with_worsening_trend_is_priority(): void
    {
        $this->assertSame(
            ['Prioritas', 'Merah', 3],
            $this->service->evaluationStatus(25.0, false, 'Memburuk')
        );
    }

    public function test_missing_previous_period_never_creates_red_status_by_itself(): void
    {
        $benchmark = [
            'value' => 20.0,
            'source' => 'Target Internal',
            'detail' => null,
            'direction' => 'Minimize',
        ];

        $controlled = $this->service->evaluateIndicator('KPI-01', 15.0, null, $benchmark);
        $attention = $this->service->evaluateIndicator('KPI-01', 25.0, null, $benchmark);

        $this->assertSame('Data Pembanding Belum Tersedia', $controlled['status_tren']);
        $this->assertSame('Terkendali', $controlled['status']);
        $this->assertSame(1, $controlled['score']);
        $this->assertSame('Perlu Perhatian', $attention['status']);
        $this->assertSame(2, $attention['score']);
    }

    public function test_priority_score_averages_only_valid_indicator_scores(): void
    {
        $priority = $this->service->priority([
            'KPI-01' => ['score' => 1],
            'KPI-02' => ['score' => 2],
            'KPI-03' => ['score' => 3],
            'KPI-04' => ['score' => null],
        ]);

        $this->assertSame(3, $priority['valid_count']);
        $this->assertEqualsWithDelta(2.0, $priority['score'], 0.000001);
        $this->assertSame('Prioritas Sedang', $priority['level']);
        $this->assertSame('Kuning', $priority['color']);
    }

    #[DataProvider('priorityBoundaryProvider')]
    public function test_priority_classification_boundaries(float $score, string $level, string $color): void
    {
        $priority = $this->service->priority([
            'indicator' => ['score' => $score],
        ]);

        $this->assertSame($level, $priority['level']);
        $this->assertSame($color, $priority['color']);
    }

    /** @return array<string, array{float, string, string}> */
    public static function priorityBoundaryProvider(): array
    {
        return [
            'batas bawah rendah' => [1.0, 'Prioritas Rendah', 'Hijau'],
            'batas bawah sedang' => [1.67, 'Prioritas Sedang', 'Kuning'],
            'batas bawah tinggi' => [2.34, 'Prioritas Tinggi', 'Merah'],
            'batas atas tinggi' => [3.0, 'Prioritas Tinggi', 'Merah'],
        ];
    }

    public function test_ranking_uses_priority_kpi_01_delta_then_alphabetical_tie_breaks(): void
    {
        $evaluations = [
            $this->rankingRow('Beta', 2.0, 10.0, 1.0),
            $this->rankingRow('Prioritas', 3.0, 1.0, -5.0),
            $this->rankingRow('KPI Tinggi', 2.0, 20.0, -3.0),
            $this->rankingRow('Delta Besar', 2.0, 10.0, 2.0),
            $this->rankingRow('Alpha', 2.0, 10.0, 1.0),
        ];

        $ranked = $this->service->rank($evaluations);

        $this->assertSame(
            ['Prioritas', 'KPI Tinggi', 'Delta Besar', 'Alpha', 'Beta'],
            array_column($ranked, 'nama_kecamatan')
        );
        $this->assertSame([1, 2, 3, 4, 5], array_column($ranked, 'rank'));
    }

    public function test_dominant_factor_uses_score_then_actual_to_benchmark_ratio(): void
    {
        $dominant = $this->service->dominantFactor([
            'KPI-02' => $this->factorIndicator(actual: 30.0, benchmark: 10.0, score: 2),
            'KPI-03' => $this->factorIndicator(actual: 15.0, benchmark: 10.0, score: 3),
            'KPI-04' => $this->factorIndicator(actual: 10.0, benchmark: 10.0, score: 3),
        ], $this->record());

        $this->assertNotNull($dominant);
        $this->assertSame('KPI-03', $dominant['code']);
        $this->assertSame('Jamban tidak layak', $dominant['label']);
        $this->assertSame('jamban', $dominant['recommendation_key']);
    }

    public function test_dominant_factor_with_zero_benchmark_compares_actual_directly(): void
    {
        $dominant = $this->service->dominantFactor([
            'KPI-02' => $this->factorIndicator(actual: 5.0, benchmark: 0.0, score: 3),
            'KPI-03' => $this->factorIndicator(actual: 20.0, benchmark: 10.0, score: 3),
        ], $this->record());

        $this->assertNotNull($dominant);
        $this->assertSame('KPI-02', $dominant['code']);
    }

    public function test_dominant_four_t_factor_uses_largest_subcategory(): void
    {
        $record = $this->record([
            'terlalu_muda' => 2,
            'terlalu_tua' => 3,
            'terlalu_dekat' => 7,
            'terlalu_banyak' => 4,
        ]);
        $dominant = $this->service->dominantFactor([
            'KPI-02' => $this->factorIndicator(actual: 5.0, benchmark: 10.0, score: 1),
            'KPI-03' => $this->factorIndicator(actual: 6.0, benchmark: 10.0, score: 1),
            'KPI-04' => $this->factorIndicator(actual: 20.0, benchmark: 10.0, score: 3),
        ], $record);

        $this->assertNotNull($dominant);
        $this->assertSame('KPI-04', $dominant['code']);
        $this->assertSame('Terlalu dekat', $dominant['subcategory']);
        $this->assertSame('PUS 4 Terlalu — Terlalu dekat', $dominant['label']);
        $this->assertSame('terlalu_dekat', $dominant['recommendation_key']);
    }

    public function test_zero_specific_risks_do_not_create_a_dominant_factor(): void
    {
        $dominant = $this->service->dominantFactor([
            'KPI-02' => $this->factorIndicator(actual: 0.0, benchmark: 0.0, score: 1),
            'KPI-03' => $this->factorIndicator(actual: 0.0, benchmark: 0.0, score: 1),
            'KPI-04' => $this->factorIndicator(actual: 0.0, benchmark: 0.0, score: 1),
        ], $this->record());

        $this->assertNull($dominant);
    }

    /**
     * @param  array<string, int>  $overrides
     * @return array<string, int>
     */
    private function record(array $overrides = []): array
    {
        return array_replace([
            'jumlah_keluarga' => 250,
            'jumlah_keluarga_sasaran' => 200,
            'total_krs' => 50,
            'tidak_berisiko' => 150,
            'air_minum_tidak_layak' => 10,
            'jamban_tidak_layak' => 5,
            'pus' => 40,
            'jumlah_4t' => 8,
            'kesejahteraan_1' => 10,
            'kesejahteraan_2' => 10,
            'kesejahteraan_3' => 10,
            'kesejahteraan_4' => 10,
            'kesejahteraan_lebih_4' => 10,
            'terlalu_muda' => 2,
            'terlalu_tua' => 2,
            'terlalu_dekat' => 2,
            'terlalu_banyak' => 2,
        ], $overrides);
    }

    /** @return array<string, mixed> */
    private function rankingRow(string $name, float $priority, float $actual, float $delta): array
    {
        return [
            'nama_kecamatan' => $name,
            'priority' => ['score' => $priority],
            'indicators' => [
                'KPI-01' => [
                    'actual' => $actual,
                    'delta' => $delta,
                ],
            ],
        ];
    }

    /** @return array{actual: float, benchmark: float, score: int} */
    private function factorIndicator(float $actual, float $benchmark, int $score): array
    {
        return compact('actual', 'benchmark', 'score');
    }
}
