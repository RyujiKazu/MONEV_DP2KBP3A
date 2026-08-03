<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Menormalkan master kecamatan Kabupaten Subang ke kode wilayah resmi.
 *
 * Seeder dijalankan manual dan tidak dipanggil oleh DatabaseSeeder. Perubahan
 * kode primer diteruskan ke tabel anak melalui foreign key ON UPDATE CASCADE.
 */
class KecamatanSubangMasterSeeder extends Seeder
{
    /** @var array<string, string> */
    public const OFFICIAL_KECAMATAN = [
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

    /** @var array<string, string> */
    private const SUPPORTED_LEGACY_KECAMATAN = [
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

    public function run(): void
    {
        DB::transaction(function (): void {
            $rows = Kecamatan::query()
                ->lockForUpdate()
                ->get(['kode_kecamatan', 'nama_kecamatan', 'created_at', 'updated_at']);

            $currentMapping = $rows
                ->sortBy('kode_kecamatan')
                ->pluck('nama_kecamatan', 'kode_kecamatan')
                ->map(fn ($name): string => (string) $name)
                ->all();

            if (
                $currentMapping !== []
                && $currentMapping !== self::SUPPORTED_LEGACY_KECAMATAN
                && $currentMapping !== self::OFFICIAL_KECAMATAN
            ) {
                throw new RuntimeException(
                    'Normalisasi dibatalkan karena keadaan master kecamatan tidak sama dengan master kosong, '
                    .'mapping legacy 13 kecamatan yang didukung, atau mapping resmi 30 kecamatan.',
                );
            }

            $officialByName = collect(self::OFFICIAL_KECAMATAN)
                ->mapWithKeys(fn (string $name, string $code): array => [
                    $this->normalizeName($name) => ['code' => $code, 'name' => $name],
                ]);
            $existingByName = [];
            $unexpected = [];

            foreach ($rows as $row) {
                $normalizedName = $this->normalizeName((string) $row->nama_kecamatan);

                if (! $officialByName->has($normalizedName)) {
                    $unexpected[] = "{$row->nama_kecamatan} ({$row->kode_kecamatan})";

                    continue;
                }

                if (isset($existingByName[$normalizedName])) {
                    throw new RuntimeException("Master kecamatan memuat nama duplikat setelah normalisasi: {$row->nama_kecamatan}.");
                }

                $existingByName[$normalizedName] = $row;
            }

            if ($unexpected !== []) {
                throw new RuntimeException(
                    'Normalisasi dibatalkan karena terdapat kecamatan di luar daftar resmi Kabupaten Subang: '
                    .implode(', ', $unexpected).'.',
                );
            }

            $alreadyNormalized = count($existingByName) === count(self::OFFICIAL_KECAMATAN);

            if ($alreadyNormalized) {
                foreach ($officialByName as $normalizedName => $official) {
                    $row = $existingByName[$normalizedName];

                    if ((string) $row->kode_kecamatan !== $official['code'] || (string) $row->nama_kecamatan !== $official['name']) {
                        $alreadyNormalized = false;
                        break;
                    }
                }
            }

            if (! $alreadyNormalized) {
                $this->moveExistingRowsToTemporaryCodes($existingByName);
                $this->writeOfficialRows($officialByName, $existingByName);
            }

            $this->validateOfficialMaster();
        });

        $this->command?->info('Master 30 kecamatan Kabupaten Subang berhasil dinormalisasi tanpa menghapus relasi data anak.');
    }

    /** @param array<string, Kecamatan> $existingByName */
    private function moveExistingRowsToTemporaryCodes(array $existingByName): void
    {
        foreach ($existingByName as $normalizedName => $row) {
            $temporaryCode = $this->temporaryCode($normalizedName);

            if (Kecamatan::query()->where('kode_kecamatan', $temporaryCode)->exists()) {
                throw new RuntimeException("Kode sementara {$temporaryCode} sudah digunakan; normalisasi dibatalkan.");
            }

            $updated = DB::table('tb_kecamatan')
                ->where('kode_kecamatan', $row->kode_kecamatan)
                ->update(['kode_kecamatan' => $temporaryCode]);

            if ($updated !== 1) {
                throw new RuntimeException("Gagal memindahkan sementara master kecamatan {$row->nama_kecamatan}.");
            }
        }
    }

    /**
     * @param  Collection<string, array{code: string, name: string}>  $officialByName
     * @param  array<string, Kecamatan>  $existingByName
     */
    private function writeOfficialRows(Collection $officialByName, array $existingByName): void
    {
        foreach ($officialByName as $normalizedName => $official) {
            $existing = $existingByName[$normalizedName] ?? null;

            if ($existing instanceof Kecamatan) {
                $updated = DB::table('tb_kecamatan')
                    ->where('kode_kecamatan', $this->temporaryCode($normalizedName))
                    ->update([
                        'kode_kecamatan' => $official['code'],
                        'nama_kecamatan' => $official['name'],
                    ]);

                if ($updated !== 1) {
                    throw new RuntimeException("Gagal menetapkan kode resmi untuk {$official['name']}.");
                }

                continue;
            }

            DB::table('tb_kecamatan')->insert([
                'kode_kecamatan' => $official['code'],
                'nama_kecamatan' => $official['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function validateOfficialMaster(): void
    {
        $rows = Kecamatan::query()
            ->orderBy('kode_kecamatan')
            ->pluck('nama_kecamatan', 'kode_kecamatan')
            ->map(fn ($name): string => (string) $name)
            ->all();

        if ($rows !== self::OFFICIAL_KECAMATAN) {
            throw new RuntimeException('Hasil normalisasi master kecamatan tidak sama dengan daftar resmi 30 kecamatan Kabupaten Subang.');
        }
    }

    private function temporaryCode(string $normalizedName): string
    {
        $officialCode = collect(self::OFFICIAL_KECAMATAN)
            ->search(fn (string $name): bool => $this->normalizeName($name) === $normalizedName, true);

        if (! is_string($officialCode)) {
            throw new RuntimeException("Nama kecamatan {$normalizedName} tidak memiliki kode resmi.");
        }

        return 'TMP-SBG-'.substr($officialCode, -2);
    }

    private function normalizeName(string $name): string
    {
        $normalized = mb_strtolower(trim($name), 'UTF-8');
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        return match ($normalized) {
            'cipunaagara' => 'cipunagara',
            'serang panjang' => 'serangpanjang',
            default => $normalized,
        };
    }
}
