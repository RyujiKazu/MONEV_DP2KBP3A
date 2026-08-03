<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use App\Models\RekapKrs;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Data simulasi untuk pengembangan lokal; bukan data aktual atau resmi DP2KBP3A.
 * Seeder ini hanya boleh dijalankan secara manual dan tidak dipanggil DatabaseSeeder.
 */
class DemoKrsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('DemoKrsSeeder tidak boleh dijalankan pada lingkungan production.');
        }

        $kecamatans = Kecamatan::query()
            ->orderBy('kode_kecamatan')
            ->get();

        if ($kecamatans->isEmpty()) {
            $this->command?->warn('Demo KRS tidak dibuat karena master kecamatan belum tersedia.');

            return;
        }

        $this->command?->warn('Membuat data SIMULASI KRS 2024-2025. Data ini bukan data aktual atau resmi DP2KBP3A.');

        $createdBy = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->value('id_user');
        $createdCount = 0;

        DB::transaction(function () use ($kecamatans, $createdBy, &$createdCount): void {
            foreach ($kecamatans as $kecamatan) {
                foreach ([2024, 2025] as $tahun) {
                    $exists = RekapKrs::query()
                        ->where('kode_kecamatan', $kecamatan->kode_kecamatan)
                        ->where('tahun', $tahun)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    RekapKrs::factory()->create([
                        'kode_kecamatan' => $kecamatan->kode_kecamatan,
                        'tahun' => $tahun,
                        'is_simulasi' => true,
                        'sumber_data' => 'Data demo acak untuk pengembangan lokal',
                        'catatan_data' => 'Bukan data aktual atau resmi DP2KBP3A Kabupaten Subang.',
                        'created_by' => $createdBy,
                    ]);

                    $createdCount++;
                }
            }
        });

        $this->command?->info("{$createdCount} baris data simulasi KRS berhasil dibuat tanpa menimpa data yang sudah ada.");
    }
}
