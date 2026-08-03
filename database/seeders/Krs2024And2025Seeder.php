<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use App\Models\RekapKrs;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Krs2024And2025Seeder extends Seeder
{
    /** @var list<string> */
    private const CSV_COLUMNS = [
        'kecamatan',
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
    ];

    /** @var list<string> */
    private const WELFARE_COLUMNS = [
        'kesejahteraan_1',
        'kesejahteraan_2',
        'kesejahteraan_3',
        'kesejahteraan_4',
        'kesejahteraan_lebih_4',
    ];

    /** @var list<string> */
    private const NON_NEGATIVE_COLUMNS = [
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
    ];

    /** @var array<string, int> */
    private const EXPECTED_2025_AGGREGATES = [
        'jumlah_keluarga' => 484838,
        'jumlah_keluarga_sasaran' => 291611,
        'total_krs' => 24344,
        'tidak_berisiko' => 267267,
        'kesejahteraan_1' => 4268,
        'kesejahteraan_2' => 2382,
        'kesejahteraan_3' => 2082,
        'kesejahteraan_4' => 1694,
        'kesejahteraan_lebih_4' => 13918,
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

    /** @var list<float> */
    private const POPULATION_FACTORS = [0.96, 0.97, 0.98, 0.99, 1.00, 1.01];

    /** @var list<float> */
    private const RISK_FACTORS = [1.08, 1.05, 1.03, 1.00, 0.97, 0.94, 0.91];

    /** @var list<float> */
    private const ENVIRONMENT_FACTORS = [1.10, 1.06, 1.03, 1.00, 0.96, 0.93];

    /** @var list<float> */
    private const REPRODUCTIVE_FACTORS = [1.07, 1.04, 1.01, 0.98, 0.95];

    private const ACTUAL_SOURCE = 'Rekapitulasi Keluarga Berisiko Stunting Berdasarkan Wilayah Kabupaten Subang Tahun 2025';

    private const ACTUAL_NOTE = 'Data utama penelitian dari DP2KBP3A Kabupaten Subang';

    private const SIMULATION_SOURCE = 'Data simulasi sementara berdasarkan struktur data KRS tahun 2025';

    private const SIMULATION_NOTE = 'Digunakan untuk pengujian perbandingan antarperiode dan harus diganti setelah data resmi tahun 2024 tersedia';

    public function run(): void
    {
        DB::transaction(function (): void {
            $rows2025 = $this->readCsv2025();
            $this->validateExpectedAggregates($rows2025);

            $kecamatanCodes = $this->resolveKecamatanCodes($rows2025);
            $adminId = User::query()
                ->where('role', User::ROLE_ADMIN)
                ->orderBy('id_user')
                ->value('id_user');
            $adminId = $adminId === null ? null : (int) $adminId;

            $this->seedActual2025($rows2025, $kecamatanCodes, $adminId);
            $this->seedSimulated2024($rows2025, $kecamatanCodes, $adminId);
            $this->validatePersistedData($kecamatanCodes);
        });

        $this->command?->info('Data KRS aktual 2025 dan simulasi 2024 berhasil disimpan untuk 30 kecamatan.');
    }

    /**
     * @return list<array<string, int|string>>
     */
    private function readCsv2025(): array
    {
        $path = database_path('data/krs_2025.csv');
        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("File data KRS 2025 tidak dapat dibaca: {$path}");
        }

        try {
            $header = fgetcsv($handle, separator: ',', enclosure: '"', escape: '');

            if ($header === false) {
                throw new RuntimeException('File data KRS 2025 kosong.');
            }

            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]) ?? (string) $header[0];

            if ($header !== self::CSV_COLUMNS) {
                throw new RuntimeException('Header file data KRS 2025 tidak sesuai dengan struktur yang diwajibkan.');
            }

            $rows = [];
            $lineNumber = 1;

            while (($values = fgetcsv($handle, separator: ',', enclosure: '"', escape: '')) !== false) {
                $lineNumber++;

                if ($values === [null] || $values === ['']) {
                    continue;
                }

                if (count($values) !== count(self::CSV_COLUMNS)) {
                    throw new RuntimeException("Jumlah kolom CSV tidak valid pada baris {$lineNumber}.");
                }

                $raw = array_combine(self::CSV_COLUMNS, $values);

                if ($raw === false) {
                    throw new RuntimeException("Baris CSV {$lineNumber} tidak dapat dipetakan.");
                }

                $name = trim((string) $raw['kecamatan']);

                if ($name === '') {
                    throw new RuntimeException("Nama kecamatan kosong pada baris {$lineNumber}.");
                }

                $row = ['kecamatan' => $name];

                foreach (array_slice(self::CSV_COLUMNS, 1) as $column) {
                    $value = trim((string) $raw[$column]);

                    if (preg_match('/^\d+$/D', $value) !== 1) {
                        throw new RuntimeException("Nilai {$column} untuk {$name} pada baris {$lineNumber} harus berupa bilangan bulat nonnegatif.");
                    }

                    $row[$column] = (int) $value;
                }

                $rows[] = $row;
            }
        } finally {
            fclose($handle);
        }

        if (count($rows) !== 30) {
            throw new RuntimeException('File data KRS 2025 harus berisi tepat 30 kecamatan; ditemukan '.count($rows).'.');
        }

        $normalizedNames = array_map(
            fn (array $row): string => $this->normalizeKecamatanName((string) $row['kecamatan']),
            $rows,
        );

        if (count(array_unique($normalizedNames)) !== 30) {
            throw new RuntimeException('File data KRS 2025 memuat nama kecamatan duplikat.');
        }

        return $rows;
    }

    /**
     * @param  list<array<string, int|string>>  $rows
     * @return list<string>
     */
    private function resolveKecamatanCodes(array $rows): array
    {
        /** @var array<string, string> $lookup */
        $lookup = [];

        foreach (Kecamatan::query()->get(['kode_kecamatan', 'nama_kecamatan']) as $kecamatan) {
            $normalizedName = $this->normalizeKecamatanName((string) $kecamatan->nama_kecamatan);
            $code = (string) $kecamatan->kode_kecamatan;

            if (isset($lookup[$normalizedName]) && $lookup[$normalizedName] !== $code) {
                throw new RuntimeException("Nama kecamatan master ambigu setelah normalisasi: {$kecamatan->nama_kecamatan}.");
            }

            $lookup[$normalizedName] = $code;
        }

        $codes = [];
        $missingNames = [];

        foreach ($rows as $row) {
            $name = (string) $row['kecamatan'];
            $code = $this->resolveKecamatanCode($name, $lookup);

            if ($code === null) {
                $missingNames[] = $name;

                continue;
            }

            $codes[] = $code;
        }

        if ($missingNames !== []) {
            throw new RuntimeException('Kecamatan berikut tidak ditemukan pada master tb_kecamatan: '.implode(', ', $missingNames).'.');
        }

        if (count($codes) !== 30 || count(array_unique($codes)) !== 30) {
            throw new RuntimeException('Pemetaan data KRS harus menghasilkan tepat 30 kode kecamatan yang unik.');
        }

        return $codes;
    }

    /**
     * @param  array<string, string>  $lookup
     */
    private function resolveKecamatanCode(string $name, array $lookup): ?string
    {
        return $lookup[$this->normalizeKecamatanName($name)] ?? null;
    }

    private function normalizeKecamatanName(string $name): string
    {
        $normalized = mb_strtolower(trim($name), 'UTF-8');
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        return match ($normalized) {
            'cipunaagara' => 'cipunagara',
            'serang panjang' => 'serangpanjang',
            default => $normalized,
        };
    }

    /**
     * @param  list<array<string, int|string>>  $rows
     * @param  list<string>  $kecamatanCodes
     */
    private function seedActual2025(array $rows, array $kecamatanCodes, ?int $adminId): void
    {
        foreach ($rows as $index => $row) {
            $name = (string) $row['kecamatan'];
            unset($row['kecamatan']);

            $attributes = [
                ...$row,
                'tahun' => 2025,
                'jumlah_4t' => null,
                'is_simulasi' => false,
                'sumber_data' => self::ACTUAL_SOURCE,
                'catatan_data' => self::ACTUAL_NOTE,
                'created_by' => $adminId,
            ];

            $this->validateRow($name, $attributes);

            RekapKrs::query()->updateOrCreate(
                ['kode_kecamatan' => $kecamatanCodes[$index], 'tahun' => 2025],
                $attributes,
            );
        }
    }

    /**
     * @param  list<array<string, int|string>>  $rows2025
     * @param  list<string>  $kecamatanCodes
     */
    private function seedSimulated2024(array $rows2025, array $kecamatanCodes, ?int $adminId): void
    {
        foreach ($rows2025 as $index => $row2025) {
            $name = (string) $row2025['kecamatan'];
            $attributes = $this->buildSimulated2024($row2025, $index, $adminId);

            $this->validateRow($name, $attributes);

            RekapKrs::query()->updateOrCreate(
                ['kode_kecamatan' => $kecamatanCodes[$index], 'tahun' => 2024],
                $attributes,
            );
        }
    }

    /**
     * @param  array<string, int|string>  $row2025
     * @return array<string, bool|int|string|null>
     */
    private function buildSimulated2024(array $row2025, int $index, ?int $adminId): array
    {
        $populationFactor = self::POPULATION_FACTORS[$index % count(self::POPULATION_FACTORS)];
        $riskFactor = self::RISK_FACTORS[$index % count(self::RISK_FACTORS)];
        $environmentFactor = self::ENVIRONMENT_FACTORS[$index % count(self::ENVIRONMENT_FACTORS)];
        $reproductiveFactor = self::REPRODUCTIVE_FACTORS[$index % count(self::REPRODUCTIVE_FACTORS)];

        $jumlahKeluarga = $this->scale((int) $row2025['jumlah_keluarga'], $populationFactor);
        $jumlahKeluargaSasaran = min(
            $jumlahKeluarga,
            $this->scale((int) $row2025['jumlah_keluarga_sasaran'], $populationFactor),
        );
        $totalKrs = min(
            $jumlahKeluargaSasaran,
            $this->scale((int) $row2025['total_krs'], $riskFactor),
        );
        $welfareAllocation = $this->allocateProportionally(
            array_map(fn (string $column): int => (int) $row2025[$column], self::WELFARE_COLUMNS),
            $totalKrs,
        );
        $pus = $this->scale((int) $row2025['pus'], $populationFactor);

        return [
            'tahun' => 2024,
            'jumlah_keluarga' => $jumlahKeluarga,
            'jumlah_keluarga_sasaran' => $jumlahKeluargaSasaran,
            'kesejahteraan_1' => $welfareAllocation[0],
            'kesejahteraan_2' => $welfareAllocation[1],
            'kesejahteraan_3' => $welfareAllocation[2],
            'kesejahteraan_4' => $welfareAllocation[3],
            'kesejahteraan_lebih_4' => $welfareAllocation[4],
            'total_krs' => $totalKrs,
            'tidak_berisiko' => $jumlahKeluargaSasaran - $totalKrs,
            'baduta' => $this->scale((int) $row2025['baduta'], $populationFactor),
            'balita' => $this->scale((int) $row2025['balita'], $populationFactor),
            'pus' => $pus,
            'pus_hamil' => min($pus, $this->scale((int) $row2025['pus_hamil'], $populationFactor)),
            'air_minum_tidak_layak' => min(
                $totalKrs,
                $this->scale((int) $row2025['air_minum_tidak_layak'], $environmentFactor),
            ),
            'jamban_tidak_layak' => min(
                $totalKrs,
                $this->scale((int) $row2025['jamban_tidak_layak'], $environmentFactor),
            ),
            'terlalu_muda' => min($pus, $this->scale((int) $row2025['terlalu_muda'], $reproductiveFactor)),
            'terlalu_tua' => min($pus, $this->scale((int) $row2025['terlalu_tua'], $reproductiveFactor)),
            'terlalu_dekat' => min($pus, $this->scale((int) $row2025['terlalu_dekat'], $reproductiveFactor)),
            'terlalu_banyak' => min($pus, $this->scale((int) $row2025['terlalu_banyak'], $reproductiveFactor)),
            'jumlah_4t' => null,
            'is_simulasi' => true,
            'sumber_data' => self::SIMULATION_SOURCE,
            'catatan_data' => self::SIMULATION_NOTE,
            'created_by' => $adminId,
        ];
    }

    private function scale(int $value, float $factor): int
    {
        return max(0, (int) round($value * $factor));
    }

    /**
     * Allocate an integer total using Hamilton's largest-remainder method.
     *
     * @param  list<int>  $weights
     * @return list<int>
     */
    private function allocateProportionally(array $weights, int $targetTotal): array
    {
        $hasNegativeWeight = array_filter($weights, fn (int $weight): bool => $weight < 0) !== [];

        if ($targetTotal < 0 || $hasNegativeWeight) {
            throw new RuntimeException('Alokasi proporsional tidak menerima nilai negatif.');
        }

        $weights = array_values($weights);
        $weightTotal = array_sum($weights);

        if ($targetTotal === 0) {
            return array_fill(0, count($weights), 0);
        }

        if ($weightTotal === 0) {
            throw new RuntimeException('Alokasi proporsional tidak dapat dilakukan karena seluruh bobot bernilai nol.');
        }

        $allocations = [];
        $remainders = [];

        foreach ($weights as $index => $weight) {
            $weightedTotal = $weight * $targetTotal;
            $allocations[$index] = intdiv($weightedTotal, $weightTotal);
            $remainders[$index] = $weightedTotal % $weightTotal;
        }

        $remaining = $targetTotal - array_sum($allocations);
        $order = array_keys($weights);
        usort($order, fn (int $left, int $right): int => ($remainders[$right] <=> $remainders[$left]) ?: ($left <=> $right));

        for ($position = 0; $position < $remaining; $position++) {
            $allocations[$order[$position]]++;
        }

        ksort($allocations);

        return array_values($allocations);
    }

    /**
     * @param  array<string, bool|int|string|null>  $row
     */
    private function validateRow(string $name, array $row): void
    {
        $label = "{$name} tahun {$row['tahun']}";

        foreach (self::NON_NEGATIVE_COLUMNS as $column) {
            $value = $row[$column] ?? null;

            if ($column === 'jumlah_4t' && $value === null) {
                continue;
            }

            if (! is_int($value) || $value < 0) {
                throw new RuntimeException("Data {$label} gagal validasi: {$column} harus berupa bilangan bulat nonnegatif.");
            }
        }

        if ($row['jumlah_keluarga_sasaran'] !== $row['total_krs'] + $row['tidak_berisiko']) {
            throw new RuntimeException("Data {$label} gagal validasi: total KRS ditambah tidak berisiko harus sama dengan jumlah keluarga sasaran.");
        }

        $welfareTotal = array_sum(array_map(
            fn (string $column): int => (int) $row[$column],
            self::WELFARE_COLUMNS,
        ));

        if ($welfareTotal !== $row['total_krs']) {
            throw new RuntimeException("Data {$label} gagal validasi: jumlah peringkat kesejahteraan harus sama dengan total KRS.");
        }

        if ($row['jumlah_keluarga_sasaran'] > $row['jumlah_keluarga']) {
            throw new RuntimeException("Data {$label} gagal validasi: jumlah keluarga sasaran melebihi jumlah keluarga.");
        }

        if ($row['total_krs'] > $row['jumlah_keluarga_sasaran']) {
            throw new RuntimeException("Data {$label} gagal validasi: total KRS melebihi jumlah keluarga sasaran.");
        }

        foreach (['air_minum_tidak_layak', 'jamban_tidak_layak'] as $column) {
            if ($row[$column] > $row['total_krs']) {
                throw new RuntimeException("Data {$label} gagal validasi: {$column} melebihi total KRS.");
            }
        }

        if ($row['pus_hamil'] > $row['pus']) {
            throw new RuntimeException("Data {$label} gagal validasi: PUS hamil melebihi jumlah PUS.");
        }

        foreach (['terlalu_muda', 'terlalu_tua', 'terlalu_dekat', 'terlalu_banyak'] as $column) {
            if ($row[$column] > $row['pus']) {
                throw new RuntimeException("Data {$label} gagal validasi: {$column} melebihi jumlah PUS.");
            }
        }

        if ($row['jumlah_4t'] !== null && $row['jumlah_4t'] > $row['pus']) {
            throw new RuntimeException("Data {$label} gagal validasi: jumlah 4T melebihi jumlah PUS.");
        }
    }

    /**
     * @param  list<array<string, int|string>>  $rows
     */
    private function validateExpectedAggregates(array $rows): void
    {
        foreach (self::EXPECTED_2025_AGGREGATES as $column => $expected) {
            $actual = array_sum(array_map(
                fn (array $row): int => (int) $row[$column],
                $rows,
            ));

            if ($actual !== $expected) {
                throw new RuntimeException("Agregat 2025 untuk {$column} tidak sesuai: diharapkan {$expected}, ditemukan {$actual}.");
            }
        }

        if (self::EXPECTED_2025_AGGREGATES['total_krs'] + self::EXPECTED_2025_AGGREGATES['tidak_berisiko'] !== self::EXPECTED_2025_AGGREGATES['jumlah_keluarga_sasaran']) {
            throw new RuntimeException('Konstanta agregat 2025 tidak konsisten untuk total KRS dan keluarga sasaran.');
        }

        $expectedWelfareTotal = array_sum(array_intersect_key(
            self::EXPECTED_2025_AGGREGATES,
            array_flip(self::WELFARE_COLUMNS),
        ));

        if ($expectedWelfareTotal !== self::EXPECTED_2025_AGGREGATES['total_krs']) {
            throw new RuntimeException('Konstanta agregat peringkat kesejahteraan 2025 tidak konsisten dengan total KRS.');
        }
    }

    /**
     * @param  list<string>  $kecamatanCodes
     */
    private function validatePersistedData(array $kecamatanCodes): void
    {
        foreach ([2024 => true, 2025 => false] as $year => $isSimulation) {
            $records = RekapKrs::query()
                ->where('tahun', $year)
                ->get();

            if ($records->count() !== 30) {
                throw new RuntimeException("Hasil seeder tahun {$year} harus berisi tepat 30 record; ditemukan {$records->count()}.");
            }

            if ($records->where('is_simulasi', $isSimulation)->count() !== 30) {
                throw new RuntimeException("Penanda simulasi data tahun {$year} tidak konsisten.");
            }

            if ($records->whereNull('jumlah_4t')->count() !== 30) {
                throw new RuntimeException("Seluruh nilai jumlah_4t tahun {$year} harus null.");
            }

            $persistedCodes = $records->pluck('kode_kecamatan')->map(fn ($code): string => (string) $code)->all();
            sort($persistedCodes);
            $expectedCodes = $kecamatanCodes;
            sort($expectedCodes);

            if ($persistedCodes !== $expectedCodes) {
                throw new RuntimeException("Pemetaan kecamatan yang tersimpan untuk tahun {$year} tidak sesuai dengan CSV.");
            }
        }

        $records2025 = RekapKrs::query()->where('tahun', 2025)->get();

        foreach (self::EXPECTED_2025_AGGREGATES as $column => $expected) {
            $actual = (int) $records2025->sum($column);

            if ($actual !== $expected) {
                throw new RuntimeException("Agregat hasil penyimpanan 2025 untuk {$column} tidak sesuai: diharapkan {$expected}, ditemukan {$actual}.");
            }
        }
    }
}
