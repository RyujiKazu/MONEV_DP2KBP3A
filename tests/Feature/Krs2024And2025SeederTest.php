<?php

namespace Tests\Feature;

use App\Models\Kecamatan;
use App\Models\RekapKrs;
use App\Services\KrsEvaluationService;
use Database\Seeders\Krs2024And2025Seeder;
use RuntimeException;

class Krs2024And2025SeederTest extends FeatureTestCase
{
    /** @var list<string> */
    private const SNAPSHOT_FIELDS = [
        'kode_kecamatan',
        'tahun',
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
        'is_simulasi',
        'sumber_data',
        'catatan_data',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createKrsSubangKecamatanMaster();
    }

    public function test_seeder_is_idempotent_deterministic_and_preserves_all_integrity_rules(): void
    {
        $this->seed(Krs2024And2025Seeder::class);

        $actual = RekapKrs::query()->where('tahun', 2025)->orderBy('kode_kecamatan')->get();
        $simulated = RekapKrs::query()->where('tahun', 2024)->orderBy('kode_kecamatan')->get();

        $this->assertCount(30, $actual);
        $this->assertCount(30, $simulated);
        $this->assertDatabaseCount('tb_rekap_krs', 60);
        $this->assertCount(60, RekapKrs::query()->get()->unique(fn (RekapKrs $row): string => $row->kode_kecamatan.'|'.$row->tahun));

        $this->assertEqualsCanonicalizing(
            self::KRS_SUBANG_KECAMATAN_NAMES,
            $actual->map(fn (RekapKrs $row): string => $row->kecamatan->nama_kecamatan)->all(),
        );

        foreach ($actual->concat($simulated) as $row) {
            $this->assertSame($row->jumlah_keluarga_sasaran, $row->total_krs + $row->tidak_berisiko);
            $this->assertSame(
                $row->total_krs,
                $row->kesejahteraan_1
                    + $row->kesejahteraan_2
                    + $row->kesejahteraan_3
                    + $row->kesejahteraan_4
                    + $row->kesejahteraan_lebih_4,
            );
            $this->assertLessThanOrEqual($row->jumlah_keluarga, $row->jumlah_keluarga_sasaran);
            $this->assertLessThanOrEqual($row->total_krs, $row->air_minum_tidak_layak);
            $this->assertLessThanOrEqual($row->total_krs, $row->jamban_tidak_layak);
            $this->assertLessThanOrEqual($row->pus, $row->pus_hamil);
            $this->assertNull($row->jumlah_4t);
        }

        foreach ($actual as $row) {
            $this->assertFalse($row->is_simulasi);
        }

        foreach ($simulated as $row) {
            $this->assertTrue($row->is_simulasi);
        }

        $expectedTotals = [
            'jumlah_keluarga' => 484838,
            'jumlah_keluarga_sasaran' => 291611,
            'kesejahteraan_1' => 4268,
            'kesejahteraan_2' => 2382,
            'kesejahteraan_3' => 2082,
            'kesejahteraan_4' => 1694,
            'kesejahteraan_lebih_4' => 13918,
            'total_krs' => 24344,
            'tidak_berisiko' => 267267,
            'baduta' => 22614,
            'balita' => 51387,
            'pus' => 234937,
            'pus_hamil' => 5252,
            'air_minum_tidak_layak' => 1713,
            'jamban_tidak_layak' => 11291,
            'terlalu_muda' => 982,
            'terlalu_tua' => 52200,
            'terlalu_dekat' => 915,
            'terlalu_banyak' => 34922,
        ];

        foreach ($expectedTotals as $field => $expected) {
            $this->assertSame($expected, $actual->sum($field), "Agregat {$field} tahun 2025 tidak sesuai sumber.");
        }

        $snapshot = $simulated->map->only(self::SNAPSHOT_FIELDS)->values()->all();

        $this->seed(Krs2024And2025Seeder::class);

        $this->assertDatabaseCount('tb_rekap_krs', 60);
        $this->assertSame(
            $snapshot,
            RekapKrs::query()
                ->where('tahun', 2024)
                ->orderBy('kode_kecamatan')
                ->get()
                ->map->only(self::SNAPSHOT_FIELDS)
                ->values()
                ->all(),
        );
    }

    public function test_nullable_kpi_04_is_excluded_from_priority_and_simulation_badges_are_visible(): void
    {
        $this->seed(Krs2024And2025Seeder::class);

        $service = app(KrsEvaluationService::class);
        $evaluation = $service->evaluateYear(2025);

        $this->assertSame(2024, $evaluation['previous_year']);
        $this->assertTrue($evaluation['has_data']);

        foreach ($evaluation['records'] as $item) {
            $this->assertNull($item['indicators']['KPI-04']['actual']);
            $this->assertSame('Data Tidak Tersedia', $item['indicators']['KPI-04']['status']);
            $this->assertNull($item['indicators']['KPI-04']['score']);
            $this->assertSame(3, $item['priority']['valid_count']);
        }

        $trendStatuses = $evaluation['records']
            ->pluck('indicators.KPI-01.status_tren')
            ->unique();
        $this->assertContains('Membaik', $trendStatuses);
        $this->assertContains('Tetap', $trendStatuses);
        $this->assertContains('Memburuk', $trendStatuses);

        $admin = $this->createUser();

        $this->actingAs($admin)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertViewHas('selectedYear', 2025)
            ->assertViewHas('evaluation', fn (array $dashboardEvaluation): bool => $dashboardEvaluation['previous_year'] === 2024)
            ->assertDontSee('Pembanding menggunakan data simulasi')
            ->assertDontSee('Data pembanding tahun 2024 merupakan data simulasi sementara untuk pengujian sistem.')
            ->assertSee('Data Tidak Tersedia');

        $csv = $this->actingAs($admin)
            ->get(route('laporan.csv', ['tahun' => 2025]))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Status data tahun berjalan', $csv);
        $this->assertStringContainsString('Status data tahun pembanding', $csv);
        $this->assertStringContainsString('Aktual', $csv);
        $this->assertStringContainsString('Simulasi', $csv);
        $this->assertStringContainsString('Rekapitulasi Keluarga Berisiko Stunting Berdasarkan Wilayah Kabupaten Subang Tahun 2025', $csv);
        $this->assertStringContainsString('Data simulasi sementara berdasarkan struktur data KRS tahun 2025', $csv);

        $this->actingAs($admin)
            ->get(route('admin.rekap-krs.index'))
            ->assertOk()
            ->assertSee('Aktual');

        $this->actingAs($admin)
            ->get(route('admin.rekap-krs.index', ['tahun' => 2024]))
            ->assertOk()
            ->assertSee('Simulasi');
    }

    public function test_name_normalization_and_required_aliases_map_to_existing_master_codes(): void
    {
        Kecamatan::query()->where('nama_kecamatan', 'Sagalaherang')->update([
            'nama_kecamatan' => '  SAGALAHERANG  ',
        ]);
        Kecamatan::query()->where('nama_kecamatan', 'Cipunagara')->update([
            'nama_kecamatan' => 'Cipunaagara',
        ]);
        Kecamatan::query()->where('nama_kecamatan', 'Serangpanjang')->update([
            'nama_kecamatan' => 'Serang  Panjang',
        ]);

        $this->seed(Krs2024And2025Seeder::class);

        $this->assertDatabaseCount('tb_rekap_krs', 60);
        $this->assertSame(2, RekapKrs::query()->where('kode_kecamatan', '32.13.18')->count());
        $this->assertSame(2, RekapKrs::query()->where('kode_kecamatan', '32.13.23')->count());
    }

    public function test_missing_kecamatan_aborts_the_whole_transaction_with_a_clear_name(): void
    {
        Kecamatan::query()->where('nama_kecamatan', 'Ciater')->delete();

        try {
            $this->seed(Krs2024And2025Seeder::class);
            $this->fail('Seeder seharusnya menolak master kecamatan yang belum lengkap.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Ciater', $exception->getMessage());
        }

        $this->assertDatabaseCount('tb_rekap_krs', 0);
    }
}
