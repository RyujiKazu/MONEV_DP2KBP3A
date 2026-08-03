<?php

namespace Tests\Feature\Concerns;

use App\Models\Kecamatan;
use App\Models\RekapKrs;
use App\Models\TargetIndikator;
use App\Models\User;
use Illuminate\Support\Arr;

trait CreatesKrsData
{
    /** @var list<string> */
    protected const KRS_SUBANG_KECAMATAN_NAMES = [
        'Sagalaherang',
        'Cisalak',
        'Subang',
        'Kalijati',
        'Pabuaran',
        'Purwadadi',
        'Pagaden',
        'Binong',
        'Ciasem',
        'Pusakanagara',
        'Pamanukan',
        'Jalancagak',
        'Blanakan',
        'Tanjungsiang',
        'Compreng',
        'Patokbeusi',
        'Cibogo',
        'Cipunagara',
        'Cijambe',
        'Cipeundeuy',
        'Legonkulon',
        'Cikaum',
        'Serangpanjang',
        'Sukasari',
        'Tambakdahan',
        'Kasomalang',
        'Dawuan',
        'Pagaden Barat',
        'Ciater',
        'Pusakajaya',
    ];

    protected function createUser(string $role = User::ROLE_ADMIN, string $password = 'rahasia-test'): User
    {
        return User::factory()->create([
            'role' => $role,
            'password' => $password,
        ]);
    }

    protected function createKecamatan(
        string $code = 'TEST-KEC-01',
        string $name = 'Kecamatan Pengujian',
    ): Kecamatan {
        return Kecamatan::query()->create([
            'kode_kecamatan' => $code,
            'nama_kecamatan' => $name,
        ]);
    }

    /**
     * Membuat master wilayah lengkap hanya di database pengujian.
     * Seeder produksi tetap wajib memakai master yang sudah tersedia.
     *
     * @return list<Kecamatan>
     */
    protected function createKrsSubangKecamatanMaster(): array
    {
        return collect(self::KRS_SUBANG_KECAMATAN_NAMES)
            ->map(fn (string $name, int $index): Kecamatan => Kecamatan::query()->create([
                'kode_kecamatan' => sprintf('32.13.%02d', $index + 1),
                'nama_kecamatan' => $name,
            ]))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validRekapPayload(Kecamatan $kecamatan, int $year = 2025, array $overrides = []): array
    {
        $record = RekapKrs::factory()->make([
            'kode_kecamatan' => $kecamatan->getKey(),
            'tahun' => $year,
        ]);

        $payload = Arr::only($record->getAttributes(), [
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
        ]);

        return array_replace($payload, $overrides);
    }

    /** @param array<string, mixed> $overrides */
    protected function createRekap(
        Kecamatan $kecamatan,
        int $year = 2025,
        array $overrides = [],
        ?User $creator = null,
    ): RekapKrs {
        return RekapKrs::query()->create([
            ...$this->validRekapPayload($kecamatan, $year, $overrides),
            'created_by' => $creator?->getKey(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validTargetPayload(array $overrides = []): array
    {
        return array_replace([
            'kode_indikator' => 'KPI-01',
            'tahun_berlaku' => 2025,
            'nilai_target' => '20.1234',
            'arah_target' => 'Minimize',
            'jenis_target' => 'Internal',
            'sumber_target' => 'Target pengujian otomatis',
            'status_aktif' => '1',
        ], $overrides);
    }

    protected function createTarget(string $code = 'KPI-01', int $year = 2025): TargetIndikator
    {
        return TargetIndikator::factory()->create([
            'kode_indikator' => $code,
            'nama_indikator' => TargetIndikator::INDIKATOR[$code],
            'tahun_berlaku' => $year,
        ]);
    }
}
