<?php

namespace Tests\Feature;

use App\Models\RekapKrs;
use App\Models\TargetIndikator;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoKrsSeeder;
use Illuminate\Support\Facades\Hash;

class SeederAndFactoryTest extends FeatureTestCase
{
    public function test_database_seeder_safely_skips_research_data_when_master_is_not_ready(): void
    {
        config([
            'monev.seed_accounts.admin.password' => '',
            'monev.seed_accounts.pkk.password' => '',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('tb_kecamatan', 0);
        $this->assertDatabaseCount('tb_rekap_krs', 0);
    }

    public function test_database_seeder_creates_configured_accounts_and_local_research_data(): void
    {
        $this->createKrsSubangKecamatanMaster();

        config([
            'monev.seed_accounts.admin' => [
                'name' => 'Admin Pengujian',
                'username' => 'admin_test',
                'password' => 'password-admin-test',
            ],
            'monev.seed_accounts.pkk' => [
                'name' => 'PKK Pengujian',
                'username' => 'pkk_test',
                'password' => 'password-pkk-test',
            ],
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('tb_kecamatan', 30);
        $this->assertDatabaseCount('tb_kelurahan', 0);
        $this->assertDatabaseCount('tb_rekap_krs', 60);
        $this->assertDatabaseCount('tb_target_indikator', 0);
        $this->assertTrue(Hash::check('password-admin-test', User::query()->where('username', 'admin_test')->sole()->password));
        $this->assertTrue(Hash::check('password-pkk-test', User::query()->where('username', 'pkk_test')->sole()->password));
    }

    public function test_rekap_and_target_factories_generate_valid_consistent_rows(): void
    {
        $kecamatan = $this->createKecamatan();
        $rekaps = RekapKrs::factory()->count(5)->create(['kode_kecamatan' => $kecamatan->getKey()]);
        $targets = TargetIndikator::factory()->count(8)->create();

        foreach ($rekaps as $rekap) {
            $this->assertLessThanOrEqual($rekap->jumlah_keluarga, $rekap->jumlah_keluarga_sasaran);
            $this->assertSame($rekap->jumlah_keluarga_sasaran, $rekap->total_krs + $rekap->tidak_berisiko);
            $this->assertSame(
                $rekap->total_krs,
                $rekap->kesejahteraan_1
                    + $rekap->kesejahteraan_2
                    + $rekap->kesejahteraan_3
                    + $rekap->kesejahteraan_4
                    + $rekap->kesejahteraan_lebih_4,
            );
            $this->assertLessThanOrEqual($rekap->total_krs, $rekap->air_minum_tidak_layak);
            $this->assertLessThanOrEqual($rekap->total_krs, $rekap->jamban_tidak_layak);
            $this->assertLessThanOrEqual($rekap->pus, $rekap->jumlah_4t);
        }

        $this->assertCount(8, $targets);
        $this->assertCount(8, $targets->unique(fn (TargetIndikator $target): string => $target->kode_indikator.'|'.$target->tahun_berlaku));
    }

    public function test_demo_seeder_uses_existing_kecamatan_without_overwriting_existing_krs(): void
    {
        $admin = $this->createUser();
        $kecamatan = $this->createKecamatan();
        $existing = $this->createRekap($kecamatan, 2024, creator: $admin);
        $originalTotal = $existing->total_krs;

        $this->seed(DemoKrsSeeder::class);

        $this->assertDatabaseCount('tb_kecamatan', 1);
        $this->assertDatabaseCount('tb_rekap_krs', 2);
        $this->assertDatabaseHas('tb_rekap_krs', [
            'id_rekap' => $existing->getKey(),
            'total_krs' => $originalTotal,
        ]);
        $this->assertDatabaseHas('tb_rekap_krs', [
            'kode_kecamatan' => $kecamatan->getKey(),
            'tahun' => 2025,
        ]);
    }
}
