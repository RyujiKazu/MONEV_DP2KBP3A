<?php

namespace Database\Factories;

use App\Models\Kecamatan;
use App\Models\RekapKrs;
use Illuminate\Database\Eloquent\Factories\Factory;
use LogicException;

/**
 * @extends Factory<RekapKrs>
 */
class RekapKrsFactory extends Factory
{
    private static int $yearOffset = 0;

    /**
     * Define a consistent KRS recap for automated tests and manual demo data.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jumlahKeluarga = fake()->numberBetween(500, 5000);
        $jumlahKeluargaSasaran = fake()->numberBetween(100, $jumlahKeluarga);
        $totalKrs = fake()->numberBetween(1, $jumlahKeluargaSasaran);
        $kesejahteraan = $this->distribute($totalKrs, 5);
        $pus = fake()->numberBetween(1, $jumlahKeluargaSasaran);
        $jumlah4t = fake()->numberBetween(0, $pus);
        $maximumYearOffset = now()->year + 1 - 2000;
        $tahun = 2000 + (self::$yearOffset++ % ($maximumYearOffset + 1));

        return [
            'kode_kecamatan' => static fn (): string => Kecamatan::query()
                ->inRandomOrder()
                ->value('kode_kecamatan')
                ?? throw new LogicException('Buat data kecamatan terlebih dahulu atau tentukan kode_kecamatan pada state factory.'),
            'tahun' => $tahun,
            'jumlah_keluarga' => $jumlahKeluarga,
            'jumlah_keluarga_sasaran' => $jumlahKeluargaSasaran,
            'kesejahteraan_1' => $kesejahteraan[0],
            'kesejahteraan_2' => $kesejahteraan[1],
            'kesejahteraan_3' => $kesejahteraan[2],
            'kesejahteraan_4' => $kesejahteraan[3],
            'kesejahteraan_lebih_4' => $kesejahteraan[4],
            'total_krs' => $totalKrs,
            'tidak_berisiko' => $jumlahKeluargaSasaran - $totalKrs,
            'baduta' => fake()->numberBetween(0, $jumlahKeluargaSasaran),
            'balita' => fake()->numberBetween(0, $jumlahKeluargaSasaran),
            'pus' => $pus,
            'pus_hamil' => fake()->numberBetween(0, $pus),
            'air_minum_tidak_layak' => fake()->numberBetween(0, $totalKrs),
            'jamban_tidak_layak' => fake()->numberBetween(0, $totalKrs),
            'terlalu_muda' => fake()->numberBetween(0, $jumlah4t),
            'terlalu_tua' => fake()->numberBetween(0, $jumlah4t),
            'terlalu_dekat' => fake()->numberBetween(0, $jumlah4t),
            'terlalu_banyak' => fake()->numberBetween(0, $jumlah4t),
            'jumlah_4t' => $jumlah4t,
            'is_simulasi' => false,
            'sumber_data' => 'Data factory untuk pengujian otomatis',
            'catatan_data' => null,
            'created_by' => null,
        ];
    }

    /**
     * Split a total into the requested number of non-negative buckets.
     *
     * @return list<int>
     */
    private function distribute(int $total, int $bucketCount): array
    {
        $remaining = $total;
        $buckets = [];

        for ($index = 1; $index < $bucketCount; $index++) {
            $value = fake()->numberBetween(0, $remaining);
            $buckets[] = $value;
            $remaining -= $value;
        }

        $buckets[] = $remaining;
        shuffle($buckets);

        return $buckets;
    }
}
