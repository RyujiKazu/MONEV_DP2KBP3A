<?php

namespace Tests\Unit;

use App\Services\KrsRecommendationService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class KrsRecommendationServiceTest extends TestCase
{
    private KrsRecommendationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new KrsRecommendationService;
    }

    #[DataProvider('dominantFactorRecommendationProvider')]
    public function test_recommendation_matches_the_dominant_factor(string $factorKey, string $expected): void
    {
        $recommendations = $this->service->forEvaluation(
            [],
            ['recommendation_key' => $factorKey],
            ['total_krs' => 0, 'kesejahteraan_1' => 0, 'kesejahteraan_2' => 0],
        );

        $this->assertSame([$expected], $recommendations);
    }

    /** @return array<string, array{string, string}> */
    public static function dominantFactorRecommendationProvider(): array
    {
        return [
            'air minum' => [
                'air_minum',
                'Melakukan koordinasi dengan perangkat daerah terkait untuk meningkatkan akses sumber air minum utama yang layak.',
            ],
            'jamban' => [
                'jamban',
                'Melakukan koordinasi intervensi sanitasi dan peningkatan akses jamban layak.',
            ],
            'terlalu muda' => [
                'terlalu_muda',
                'Meningkatkan edukasi pendewasaan usia perkawinan dan persiapan kehidupan berkeluarga.',
            ],
            'terlalu tua' => [
                'terlalu_tua',
                'Meningkatkan konseling kesehatan reproduksi dan perencanaan kehamilan.',
            ],
            'terlalu dekat' => [
                'terlalu_dekat',
                'Meningkatkan konseling pengaturan jarak kelahiran dan pelayanan keluarga berencana.',
            ],
            'terlalu banyak' => [
                'terlalu_banyak',
                'Meningkatkan konseling keluarga berencana dan penggunaan alat kontrasepsi.',
            ],
            'PUS 4 Terlalu tanpa subkategori' => [
                'pus_4t',
                'Meningkatkan konseling kesehatan reproduksi, perencanaan kehamilan, dan pelayanan keluarga berencana.',
            ],
        ];
    }

    public function test_high_kpi_01_adds_general_risk_recommendation(): void
    {
        $recommendations = $this->service->forEvaluation([
            'KPI-01' => [
                'actual' => 35.0,
                'meets_benchmark' => false,
                'status_tren' => 'Tetap',
            ],
        ], null, ['total_krs' => 0]);

        $this->assertContains(
            'Melakukan verifikasi kondisi wilayah dan memprioritaskan koordinasi intervensi terhadap keluarga berisiko.',
            $recommendations
        );
    }

    public function test_low_welfare_majority_adds_social_protection_recommendation(): void
    {
        $recommendations = $this->service->forEvaluation([], null, [
            'total_krs' => 10,
            'kesejahteraan_1' => 4,
            'kesejahteraan_2' => 2,
        ]);

        $this->assertContains(
            'Melakukan verifikasi dan koordinasi dengan program perlindungan sosial atau perangkat daerah terkait.',
            $recommendations
        );
    }

    public function test_worsening_indicator_adds_monitoring_recommendation(): void
    {
        $recommendations = $this->service->forEvaluation([
            'KPI-02' => ['status_tren' => 'Memburuk'],
        ], null, ['total_krs' => 0]);

        $this->assertContains(
            'Perkuat pemantauan pada periode berikutnya karena indikator mengalami peningkatan risiko.',
            $recommendations
        );
    }

    public function test_no_identified_risk_returns_periodic_monitoring_recommendation(): void
    {
        $recommendations = $this->service->forEvaluation([
            'KPI-01' => [
                'actual' => 10.0,
                'meets_benchmark' => true,
                'status_tren' => 'Tetap',
            ],
        ], null, [
            'total_krs' => 10,
            'kesejahteraan_1' => 2,
            'kesejahteraan_2' => 2,
        ]);

        $this->assertSame(
            ['Pertahankan pemantauan berkala dan verifikasi konsistensi data pada periode berikutnya.'],
            $recommendations
        );
    }
}
