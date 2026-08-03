<?php

namespace Database\Factories;

use App\Models\TargetIndikator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TargetIndikator>
 */
class TargetIndikatorFactory extends Factory
{
    private static int $combinationOffset = 0;

    /**
     * Define a target intended only for automated tests.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $codes = array_keys(TargetIndikator::INDIKATOR);
        $offset = self::$combinationOffset++;
        $yearCount = now()->year + 1 - 2000 + 1;
        $code = $codes[$offset % count($codes)];
        $year = 2000 + (intdiv($offset, count($codes)) % $yearCount);

        return [
            'kode_indikator' => $code,
            'nama_indikator' => TargetIndikator::INDIKATOR[$code],
            'tahun_berlaku' => $year,
            'nilai_target' => fake()->randomFloat(4, 0, 100),
            'arah_target' => 'Minimize',
            'jenis_target' => 'Internal',
            'sumber_target' => null,
            'status_aktif' => true,
        ];
    }
}
