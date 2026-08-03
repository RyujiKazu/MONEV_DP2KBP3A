<?php

namespace Tests\Feature;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\RekapKrs;
use Database\Seeders\KecamatanSubangMasterSeeder;
use Database\Seeders\Krs2024And2025Seeder;
use Illuminate\Support\Facades\Schema;

class KecamatanSubangMasterSeederTest extends FeatureTestCase
{
    /** @var array<string, string> */
    private const LEGACY_KECAMATAN = [
        '32.13.01' => 'Cibogo',
        '32.13.02' => 'Subang',
        '32.13.03' => 'Kalijati',
        '32.13.04' => 'Pabuaran',
        '32.13.05' => 'Cijambe',
        '32.13.06' => 'Binong',
        '32.13.07' => 'Patokbeusi',
        '32.13.08' => 'Purwadadi',
        '32.13.09' => 'Jalancagak',
        '32.13.10' => 'Legonkulon',
        '32.13.11' => 'Pamanukan',
        '32.13.12' => 'Ciasem',
        '32.13.13' => 'Pagaden',
    ];

    /** @var array<string, string> */
    private const OFFICIAL_KECAMATAN = [
        '32.13.01' => 'Sagalaherang',
        '32.13.02' => 'Cisalak',
        '32.13.03' => 'Subang',
        '32.13.04' => 'Kalijati',
        '32.13.05' => 'Pabuaran',
        '32.13.06' => 'Purwadadi',
        '32.13.07' => 'Pagaden',
        '32.13.08' => 'Binong',
        '32.13.09' => 'Ciasem',
        '32.13.10' => 'Pusakanagara',
        '32.13.11' => 'Pamanukan',
        '32.13.12' => 'Jalancagak',
        '32.13.13' => 'Blanakan',
        '32.13.14' => 'Tanjungsiang',
        '32.13.15' => 'Compreng',
        '32.13.16' => 'Patokbeusi',
        '32.13.17' => 'Cibogo',
        '32.13.18' => 'Cipunagara',
        '32.13.19' => 'Cijambe',
        '32.13.20' => 'Cipeundeuy',
        '32.13.21' => 'Legonkulon',
        '32.13.22' => 'Cikaum',
        '32.13.23' => 'Serangpanjang',
        '32.13.24' => 'Sukasari',
        '32.13.25' => 'Tambakdahan',
        '32.13.26' => 'Kasomalang',
        '32.13.27' => 'Dawuan',
        '32.13.28' => 'Pagaden Barat',
        '32.13.29' => 'Ciater',
        '32.13.30' => 'Pusakajaya',
    ];

    public function test_seeder_normalizes_legacy_master_preserves_relations_and_supports_krs_seed_data(): void
    {
        foreach (self::LEGACY_KECAMATAN as $code => $name) {
            Kecamatan::query()->create([
                'kode_kecamatan' => $code,
                'nama_kecamatan' => $name,
            ]);
        }

        $kelurahan = Kelurahan::query()->create([
            'kode_kelurahan' => '32.13.01.0001',
            'kode_kecamatan' => '32.13.01',
            'nama_kelurahan' => 'Kelurahan Cibogo Legacy',
        ]);

        $this->assertDatabaseCount('tb_kecamatan', 13);
        $this->assertKelurahanForeignKeyCascadesOnUpdate();

        $this->seed(KecamatanSubangMasterSeeder::class);

        $this->assertSame(self::OFFICIAL_KECAMATAN, $this->persistedKecamatanMapping());
        $this->assertDatabaseCount('tb_kecamatan', 30);
        $this->assertSame('32.13.17', $kelurahan->refresh()->kode_kecamatan);
        $this->assertSame('Cibogo', $kelurahan->kecamatan()->value('nama_kecamatan'));

        $firstRunMapping = $this->persistedKecamatanMapping();

        $this->seed(KecamatanSubangMasterSeeder::class);

        $this->assertSame($firstRunMapping, $this->persistedKecamatanMapping());
        $this->assertDatabaseCount('tb_kecamatan', 30);
        $this->assertDatabaseCount('tb_kelurahan', 1);
        $this->assertSame('32.13.17', $kelurahan->refresh()->kode_kecamatan);

        $this->seed(Krs2024And2025Seeder::class);

        $this->assertDatabaseCount('tb_rekap_krs', 60);
        $this->assertSame(30, RekapKrs::query()->where('tahun', 2024)->count());
        $this->assertSame(30, RekapKrs::query()->where('tahun', 2025)->count());
        $this->assertSame(
            array_keys(self::OFFICIAL_KECAMATAN),
            RekapKrs::query()
                ->select('kode_kecamatan')
                ->distinct()
                ->orderBy('kode_kecamatan')
                ->pluck('kode_kecamatan')
                ->all(),
        );
    }

    /** @return array<string, string> */
    private function persistedKecamatanMapping(): array
    {
        return Kecamatan::query()
            ->orderBy('kode_kecamatan')
            ->pluck('nama_kecamatan', 'kode_kecamatan')
            ->map(fn ($name): string => (string) $name)
            ->all();
    }

    private function assertKelurahanForeignKeyCascadesOnUpdate(): void
    {
        $foreignKey = collect(Schema::getForeignKeys('tb_kelurahan'))
            ->first(fn (array $key): bool => $key['foreign_table'] === 'tb_kecamatan'
                && $key['columns'] === ['kode_kecamatan']
                && $key['foreign_columns'] === ['kode_kecamatan']);

        $this->assertNotNull($foreignKey, 'Foreign key kelurahan ke kecamatan tidak ditemukan.');
        $this->assertSame('cascade', strtolower((string) $foreignKey['on_update']));
    }
}
