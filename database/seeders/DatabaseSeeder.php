<?php

namespace Database\Seeders;

use App\Models\EvaluasiKrs;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['username' => 'admin_dp2kbp3a'],
            [
                'nama_lengkap' => 'Admin DP2KBP3A',
                'password' => Hash::make('admin123'),
                'role' => 'Admin',
                'created_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['username' => 'pkk_dp2kbp3a'],
            [
                'nama_lengkap' => 'PKK DP2KBP3A',
                'password' => Hash::make('pkk123'),
                'role' => 'PKK',
                'created_at' => now(),
            ]
        );

        $kecamatanData = [
            ['kode' => '32.13.01', 'nama' => 'Cibogo'],
            ['kode' => '32.13.02', 'nama' => 'Subang'],
            ['kode' => '32.13.03', 'nama' => 'Kaliijati'],
            ['kode' => '32.13.04', 'nama' => 'Pabuaran'],
            ['kode' => '32.13.05', 'nama' => 'Cijambe'],
            ['kode' => '32.13.06', 'nama' => 'Binong'],
            ['kode' => '32.13.07', 'nama' => 'Patokbeusi'],
            ['kode' => '32.13.08', 'nama' => 'Purwadadi'],
            ['kode' => '32.13.09', 'nama' => 'Jalancagak'],
            ['kode' => '32.13.10', 'nama' => 'Legonkulon'],
        ];

        foreach ($kecamatanData as $index => $item) {
            Kecamatan::updateOrCreate(
                ['kode_kecamatan' => $item['kode']],
                ['nama_kecamatan' => $item['nama']]
            );

            Kelurahan::updateOrCreate(
                ['kode_kelurahan' => $item['kode'] . '.01'],
                [
                    'kode_kecamatan' => $item['kode'],
                    'nama_kelurahan' => $item['nama'] . ' Utara',
                ]
            );

            $desil2024 = $this->buildDesilValues($index + 1, 2024);
            $desil2025 = $this->buildDesilValues($index + 2, 2025);

            EvaluasiKrs::updateOrCreate(
                [
                    'kode_kecamatan' => $item['kode'],
                    'kode_kelurahan' => $item['kode'] . '.01',
                    'periode_evaluasi' => '2024-07-01',
                ],
                array_merge([
                    'kode_kecamatan' => $item['kode'],
                    'kode_kelurahan' => $item['kode'] . '.01',
                    'periode_evaluasi' => '2024-07-01',
                    'jumlah_keluarga' => 110 + ($index * 8),
                    'jumlah_keluarga_sasaran' => 88 + ($index * 6),
                    'peringkat_1' => 6 + $index,
                    'peringkat_2' => 10 + $index,
                    'peringkat_3' => 8 + $index,
                    'peringkat_4' => 5 + $index,
                    'peringkat_lebih_4' => 3 + ($index % 3),
                    'total_berisiko' => (6 + $index) + (10 + $index) + (8 + $index) + (5 + $index) + (3 + ($index % 3)),
                    'tidak_berisiko' => (88 + ($index * 6)) - (((6 + $index) + (10 + $index) + (8 + $index) + (5 + $index) + (3 + ($index % 3)))),
                    'sasaran_baduta' => 12 + $index,
                    'sasaran_balita' => 15 + $index,
                    'sasaran_pus' => 22 + $index,
                    'sasaran_pus_hamil' => 7 + ($index % 4),
                    'desil_1' => $desil2024[1],
                    'desil_2' => $desil2024[2],
                    'desil_3' => $desil2024[3],
                    'desil_4' => $desil2024[4],
                    'desil_5' => $desil2024[5],
                    'desil_6' => $desil2024[6],
                    'desil_7' => $desil2024[7],
                    'desil_8' => $desil2024[8],
                    'desil_9' => $desil2024[9],
                    'desil_10' => $desil2024[10],
                    'air_tidak_layak' => 4 + ($index % 4),
                    'jamban_tidak_layak' => 3 + ($index % 3),
                    'terlalu_muda' => 2 + ($index % 2),
                    'terlalu_tua' => 1 + ($index % 3),
                    'terlalu_dekat' => 3 + ($index % 2),
                    'terlalu_banyak' => 2 + ($index % 4),
                    'jumlah_terlalu' => (2 + ($index % 2)) + (1 + ($index % 3)) + (3 + ($index % 2)) + (2 + ($index % 4)),
                ])
            );

            EvaluasiKrs::updateOrCreate(
                [
                    'kode_kecamatan' => $item['kode'],
                    'kode_kelurahan' => $item['kode'] . '.01',
                    'periode_evaluasi' => '2025-07-01',
                ],
                array_merge([
                    'kode_kecamatan' => $item['kode'],
                    'kode_kelurahan' => $item['kode'] . '.01',
                    'periode_evaluasi' => '2025-07-01',
                    'jumlah_keluarga' => 118 + ($index * 9),
                    'jumlah_keluarga_sasaran' => 94 + ($index * 7),
                    'peringkat_1' => 7 + $index,
                    'peringkat_2' => 11 + $index,
                    'peringkat_3' => 9 + $index,
                    'peringkat_4' => 6 + $index,
                    'peringkat_lebih_4' => 4 + ($index % 3),
                    'total_berisiko' => (7 + $index) + (11 + $index) + (9 + $index) + (6 + $index) + (4 + ($index % 3)),
                    'tidak_berisiko' => (94 + ($index * 7)) - (((7 + $index) + (11 + $index) + (9 + $index) + (6 + $index) + (4 + ($index % 3)))),
                    'sasaran_baduta' => 13 + $index,
                    'sasaran_balita' => 16 + $index,
                    'sasaran_pus' => 24 + $index,
                    'sasaran_pus_hamil' => 8 + ($index % 4),
                    'desil_1' => $desil2025[1],
                    'desil_2' => $desil2025[2],
                    'desil_3' => $desil2025[3],
                    'desil_4' => $desil2025[4],
                    'desil_5' => $desil2025[5],
                    'desil_6' => $desil2025[6],
                    'desil_7' => $desil2025[7],
                    'desil_8' => $desil2025[8],
                    'desil_9' => $desil2025[9],
                    'desil_10' => $desil2025[10],
                    'air_tidak_layak' => 5 + ($index % 4),
                    'jamban_tidak_layak' => 4 + ($index % 3),
                    'terlalu_muda' => 2 + (($index + 1) % 2),
                    'terlalu_tua' => 2 + ($index % 3),
                    'terlalu_dekat' => 4 + ($index % 2),
                    'terlalu_banyak' => 3 + ($index % 4),
                    'jumlah_terlalu' => (2 + (($index + 1) % 2)) + (2 + ($index % 3)) + (4 + ($index % 2)) + (3 + ($index % 4)),
                ])
            );
        }
    }

    private function buildDesilValues(int $seed, int $year): array
    {
        $values = [];
        $base = $year === 2024 ? 6 : 8;

        for ($desil = 1; $desil <= 10; $desil++) {
            $values[$desil] = $base + (($seed + $desil) % 5) + $desil;
        }

        return $values;
    }
}
