<?php

namespace Tests\Feature;

use App\Models\TargetIndikator;
use App\Services\KrsEvaluationService;

class EvaluationWorkflowTest extends FeatureTestCase
{
    public function test_active_target_takes_precedence_and_missing_target_uses_weighted_county_aggregate(): void
    {
        $firstDistrict = $this->createKecamatan('TEST-KEC-01', 'Kecamatan Pertama');
        $secondDistrict = $this->createKecamatan('TEST-KEC-02', 'Kecamatan Kedua');
        $first = $this->createRekap($firstDistrict, 2025);
        $second = $this->createRekap($secondDistrict, 2025);

        TargetIndikator::query()->create([
            'kode_indikator' => 'KPI-01',
            'nama_indikator' => TargetIndikator::INDIKATOR['KPI-01'],
            'tahun_berlaku' => 2025,
            'nilai_target' => 12.5,
            'arah_target' => 'Minimize',
            'jenis_target' => 'Regulatif',
            'sumber_target' => 'Dokumen target pengujian',
            'status_aktif' => true,
        ]);

        $result = app(KrsEvaluationService::class)->evaluateYear(2025);
        $firstEvaluation = $result['records']->firstWhere('kode_kecamatan', $firstDistrict->getKey());

        $this->assertSame('Target Regulatif', $firstEvaluation['indicators']['KPI-01']['benchmark_source']);
        $this->assertEqualsWithDelta(12.5, $firstEvaluation['indicators']['KPI-01']['benchmark'], 0.000001);
        $this->assertSame('Dokumen target pengujian', $firstEvaluation['indicators']['KPI-01']['benchmark_detail']);

        $expectedCountyKpi02 = (($first->air_minum_tidak_layak + $second->air_minum_tidak_layak)
            / ($first->total_krs + $second->total_krs)) * 100;

        $this->assertSame('Agregat Kabupaten', $firstEvaluation['indicators']['KPI-02']['benchmark_source']);
        $this->assertEqualsWithDelta(
            $expectedCountyKpi02,
            $firstEvaluation['indicators']['KPI-02']['benchmark'],
            0.000001,
        );
    }
}
