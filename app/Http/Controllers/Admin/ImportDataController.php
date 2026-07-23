<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvaluasiKrs;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ImportDataController extends Controller
{
    private const NUMERIC_FIELDS = [
        'jumlah_keluarga',
        'jumlah_keluarga_sasaran',
        'peringkat_1',
        'peringkat_2',
        'peringkat_3',
        'peringkat_4',
        'peringkat_lebih_4',
        'total_berisiko',
        'tidak_berisiko',
        'sasaran_baduta',
        'sasaran_balita',
        'sasaran_pus',
        'sasaran_pus_hamil',
        'desil_1',
        'desil_2',
        'desil_3',
        'desil_4',
        'desil_5',
        'desil_6',
        'desil_7',
        'desil_8',
        'desil_9',
        'desil_10',
            'desil_1',
            'desil_2',
            'desil_3',
            'desil_4',
            'desil_5',
            'desil_6',
            'desil_7',
            'desil_8',
            'desil_9',
            'desil_10',
        'air_tidak_layak',
        'jamban_tidak_layak',
        'terlalu_muda',
        'terlalu_tua',
        'terlalu_dekat',
        'terlalu_banyak',
        'jumlah_terlalu',
    ];

    private function ensureAuthenticated(): void
    {
        abort_unless(Auth::check(), 403);
    }

    public function index(Request $request)
    {
        $this->ensureAuthenticated();

        $editingRecord = null;

        if ($request->filled('edit')) {
            $editingRecord = EvaluasiKrs::query()->findOrFail($request->integer('edit'));
        }

        $kecamatans = Kecamatan::query()->orderBy('nama_kecamatan')->get();
        $kelurahans = Kelurahan::query()->orderBy('nama_kelurahan')->get();
        $records = EvaluasiKrs::query()
            ->with(['kecamatan', 'kelurahan'])
            ->orderByDesc('periode_evaluasi')
            ->orderByDesc('id_rekap')
            ->get();

        return view('admin.import-data', compact('kecamatans', 'kelurahans', 'records', 'editingRecord'));
    }

    public function store(Request $request)
    {
        $this->ensureAuthenticated();

        $data = $this->validateEvaluationPayload($request->all());

        EvaluasiKrs::updateOrCreate(
            [
                'kode_kecamatan' => $data['kode_kecamatan'],
                'kode_kelurahan' => $data['kode_kelurahan'],
                'periode_evaluasi' => $data['periode_evaluasi'],
            ],
            $data
        );

        return redirect()->route('admin.import-data.index')->with('success', 'Data indikator berhasil disimpan.');
    }

    public function edit(EvaluasiKrs $record)
    {
        $this->ensureAuthenticated();

        return redirect()->route('admin.import-data.index', ['edit' => $record->id_rekap]);
    }

    public function update(Request $request, EvaluasiKrs $record)
    {
        $this->ensureAuthenticated();

        $data = $this->validateEvaluationPayload($request->all());

        $record->update($data);

        return redirect()->route('admin.import-data.index')->with('success', 'Data indikator berhasil diperbarui.');
    }

    public function destroy(EvaluasiKrs $record)
    {
        $this->ensureAuthenticated();

        $record->delete();

        return redirect()->route('admin.import-data.index')->with('success', 'Data indikator berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $this->ensureAuthenticated();

        $validated = $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt,xlsx'],
        ]);

        $file = $validated['import_file'];
        $rows = $this->readRowsFromFile($file);

        if (empty($rows)) {
            return back()->withErrors(['import_file' => 'File import tidak memiliki data yang bisa diproses.']);
        }

        $normalizedRows = [];
        $rowErrors = [];

        foreach ($rows as $index => $row) {
            try {
                $normalizedRows[] = $this->validateEvaluationPayload($row, $index + 2);
            } catch (ValidationException $exception) {
                $messages = collect($exception->errors())->flatten()->implode(' ');
                $rowErrors[] = 'Baris ' . ($index + 2) . ': ' . $messages;
            }
        }

        if (! empty($rowErrors)) {
            return back()->withErrors(['import_file' => implode(' | ', $rowErrors)]);
        }

        DB::transaction(function () use ($normalizedRows): void {
            foreach ($normalizedRows as $row) {
                EvaluasiKrs::updateOrCreate(
                    [
                        'kode_kecamatan' => $row['kode_kecamatan'],
                        'kode_kelurahan' => $row['kode_kelurahan'],
                        'periode_evaluasi' => $row['periode_evaluasi'],
                    ],
                    $row
                );
            }
        });

        return redirect()->route('admin.import-data.index')->with('success', 'File berhasil diimpor dan divalidasi.');
    }

    private function validateEvaluationPayload(array $input, ?int $rowNumber = null): array
    {
        $data = $this->normalizeEvaluationPayload($input);

        $rules = [
            'kode_kecamatan' => ['required', 'exists:tb_kecamatan,kode_kecamatan'],
            'kode_kelurahan' => ['nullable', 'exists:tb_kelurahan,kode_kelurahan'],
            'periode_evaluasi' => ['required', 'date'],
            'jumlah_keluarga' => ['required', 'integer', 'min:0'],
            'jumlah_keluarga_sasaran' => ['required', 'integer', 'min:0'],
        ];

        foreach (self::NUMERIC_FIELDS as $field) {
            $rules[$field] = ['required', 'integer', 'min:0'];
        }

        $validator = Validator::make($data, $rules);

        $validator->after(function ($validator) use ($data): void {
            $riskDetailTotal = (int) $data['peringkat_1']
                + (int) $data['peringkat_2']
                + (int) $data['peringkat_3']
                + (int) $data['peringkat_4']
                + (int) $data['peringkat_lebih_4'];

            if ((int) $data['total_berisiko'] !== $riskDetailTotal) {
                $validator->errors()->add(
                    'total_berisiko',
                    'Total Berisiko harus sama dengan jumlah Peringkat 1 sampai Peringkat Lebih 4.'
                );
            }

            $tooTotal = (int) $data['terlalu_muda']
                + (int) $data['terlalu_tua']
                + (int) $data['terlalu_dekat']
                + (int) $data['terlalu_banyak'];

            if ((int) $data['jumlah_terlalu'] !== $tooTotal) {
                $validator->errors()->add(
                    'jumlah_terlalu',
                    'Jumlah Terlalu harus sama dengan jumlah Terlalu Muda, Terlalu Tua, Terlalu Dekat, dan Terlalu Banyak.'
                );
            }

            if ((int) $data['jumlah_keluarga_sasaran'] !== ((int) $data['total_berisiko'] + (int) $data['tidak_berisiko'])) {
                $validator->errors()->add(
                    'jumlah_keluarga_sasaran',
                    'Jumlah Keluarga Sasaran harus sama dengan Total Berisiko ditambah Tidak Berisiko.'
                );
            }
        });

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return $data;
    }

    private function normalizeEvaluationPayload(array $input): array
    {
        $data = [];

        $data['kode_kecamatan'] = $this->normalizeString($input['kode_kecamatan'] ?? null);
        $data['kode_kelurahan'] = $this->normalizeString($input['kode_kelurahan'] ?? null);
        $data['periode_evaluasi'] = $this->normalizeDateValue($input['periode_evaluasi'] ?? null);

        foreach (self::NUMERIC_FIELDS as $field) {
            $data[$field] = $this->normalizeInteger($input[$field] ?? 0);
        }

        return $data;
    }

    private function normalizeString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === '' || $value === null ? null : (string) $value;
    }

    private function normalizeInteger(mixed $value): int
    {
        if ($value === '' || $value === null) {
            return 0;
        }

        return (int) $value;
    }

    private function normalizeDateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $serial = (float) $value;

            if ($serial > 30000) {
                return Carbon::create(1899, 12, 30)->addDays((int) $serial)->toDateString();
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function readRowsFromFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'csv', 'txt' => $this->readCsvRows($file->getRealPath()),
            'xlsx' => $this->readXlsxRows($file->getRealPath()),
            default => throw ValidationException::withMessages([
                'import_file' => 'Format file harus CSV atau XLSX.',
            ]),
        };
    }

    private function readCsvRows(string|false $path): array
    {
        if (! $path) {
            throw ValidationException::withMessages([
                'import_file' => 'File import tidak dapat dibaca.',
            ]);
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'import_file' => 'File import tidak dapat dibuka.',
            ]);
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            return [];
        }

        $headers = array_map([$this, 'normalizeHeader'], $header);
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $values = array_map(static fn ($value) => is_string($value) ? trim($value) : $value, $row);

            if ($this->rowIsEmpty($values)) {
                continue;
            }

            $rows[] = $this->combineRow($headers, $values);
        }

        fclose($handle);

        return $rows;
    }

    private function readXlsxRows(string|false $path): array
    {
        if (! $path) {
            throw ValidationException::withMessages([
                'import_file' => 'File import tidak dapat dibaca.',
            ]);
        }

        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages([
                'import_file' => 'File XLSX tidak valid atau rusak.',
            ]);
        }

        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($sharedStringsXml !== false) {
            $sharedStrings = $this->parseSharedStrings($sharedStringsXml);
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw ValidationException::withMessages([
                'import_file' => 'File XLSX tidak memiliki sheet pertama yang bisa dibaca.',
            ]);
        }

        $sheet = simplexml_load_string($sheetXml);

        if ($sheet === false) {
            throw ValidationException::withMessages([
                'import_file' => 'Isi file XLSX tidak dapat diparse.',
            ]);
        }

        $sheet->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = [];
        $headers = [];

        foreach ($sheet->xpath('//a:sheetData/a:row') ?: [] as $index => $row) {
            $cells = [];

            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                $columnLetters = preg_replace('/\d+$/', '', $reference);
                $columnIndex = $this->columnLettersToIndex($columnLetters);
                $cells[$columnIndex] = $this->readXlsxCell($cell, $sharedStrings);
            }

            ksort($cells);
            $values = array_values($cells);

            if ($index === 0) {
                $headers = array_map([$this, 'normalizeHeader'], $values);
                continue;
            }

            if ($this->rowIsEmpty($values)) {
                continue;
            }

            $rows[] = $this->combineRow($headers, $values);
        }

        return $rows;
    }

    private function parseSharedStrings(string $xml): array
    {
        $shared = simplexml_load_string($xml);

        if ($shared === false) {
            return [];
        }

        $shared->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $strings = [];

        foreach ($shared->xpath('//a:si') ?: [] as $item) {
            $textParts = [];

            foreach ($item->xpath('.//a:t') ?: [] as $textNode) {
                $textParts[] = (string) $textNode;
            }

            $strings[] = implode('', $textParts);
        }

        return $strings;
    }

    private function readXlsxCell(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];

        return match ($type) {
            's' => $sharedStrings[(int) ($cell->v ?? 0)] ?? '',
            'inlineStr' => trim((string) ($cell->is->t ?? '')),
            'b' => ((string) ($cell->v ?? '0')) === '1' ? '1' : '0',
            default => trim((string) ($cell->v ?? '')),
        };
    }

    private function columnLettersToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;

        foreach (str_split($letters) as $character) {
            $index = ($index * 26) + (ord($character) - 64);
        }

        return $index - 1;
    }

    private function combineRow(array $headers, array $values): array
    {
        $normalizedValues = array_values(array_slice(array_pad($values, count($headers), ''), 0, count($headers)));

        return array_combine($headers, $normalizedValues) ?: [];
    }

    private function normalizeHeader(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? $value;

        return trim($value, '_');
    }

    private function rowIsEmpty(array $values): bool
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }
}