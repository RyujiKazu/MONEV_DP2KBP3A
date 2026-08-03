<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class BaseRekapKrsRequest extends FormRequest
{
    protected const ANGKA_FIELDS = [
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

    public function authorize(): bool
    {
        return $this->user()?->role === 'Admin';
    }

    /** @return array<string, mixed> */
    protected function baseRules(): array
    {
        $rules = [
            'kode_kecamatan' => ['required', 'string', 'max:20', 'exists:tb_kecamatan,kode_kecamatan'],
            'tahun' => ['required', 'integer', 'between:2000,'.(now()->year + 1)],
            'jumlah_4t' => ['nullable', 'integer', 'min:0'],
            'is_simulasi' => ['sometimes', 'boolean'],
            'sumber_data' => ['nullable', 'string', 'max:255'],
            'catatan_data' => ['nullable', 'string'],
        ];

        foreach (self::ANGKA_FIELDS as $field) {
            $rules[$field] = ['required', 'integer', 'min:0'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $value = fn (string $field): int => (int) $this->input($field, 0);
            $jumlahSasaran = $value('jumlah_keluarga_sasaran');
            $totalKrs = $value('total_krs');

            if ($jumlahSasaran > $value('jumlah_keluarga')) {
                $validator->errors()->add('jumlah_keluarga_sasaran', 'Jumlah keluarga sasaran tidak boleh melebihi jumlah keluarga. Periksa kembali dokumen sumber.');
            }

            if ($totalKrs > $jumlahSasaran) {
                $validator->errors()->add('total_krs', 'Total KRS tidak boleh melebihi jumlah keluarga sasaran. Periksa kembali dokumen sumber.');
            }

            if ($totalKrs + $value('tidak_berisiko') !== $jumlahSasaran) {
                $validator->errors()->add('tidak_berisiko', 'Total KRS ditambah keluarga tidak berisiko harus sama dengan jumlah keluarga sasaran. Periksa kembali dokumen sumber.');
            }

            $totalKesejahteraan = collect([
                'kesejahteraan_1',
                'kesejahteraan_2',
                'kesejahteraan_3',
                'kesejahteraan_4',
                'kesejahteraan_lebih_4',
            ])->sum(fn (string $field): int => $value($field));

            if ($totalKesejahteraan !== $totalKrs) {
                $validator->errors()->add('kesejahteraan_lebih_4', 'Jumlah seluruh peringkat kesejahteraan harus sama dengan total KRS. Periksa kembali dokumen sumber.');
            }

            if ($value('air_minum_tidak_layak') > $totalKrs) {
                $validator->errors()->add('air_minum_tidak_layak', 'Jumlah KRS tanpa air minum layak tidak boleh melebihi total KRS.');
            }

            if ($value('jamban_tidak_layak') > $totalKrs) {
                $validator->errors()->add('jamban_tidak_layak', 'Jumlah KRS tanpa jamban layak tidak boleh melebihi total KRS.');
            }

            if ($value('pus_hamil') > $value('pus')) {
                $validator->errors()->add('pus_hamil', 'Jumlah PUS hamil tidak boleh melebihi jumlah PUS.');
            }

            $jumlah4t = $this->input('jumlah_4t');

            if ($jumlah4t !== null && $jumlah4t !== '' && (int) $jumlah4t > $value('pus')) {
                $validator->errors()->add('jumlah_4t', 'Jumlah PUS 4 Terlalu tidak boleh melebihi jumlah PUS.');
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'string' => ':Attribute harus berupa teks.',
            'integer' => ':Attribute harus berupa bilangan bulat.',
            'min.numeric' => ':Attribute tidak boleh bernilai negatif.',
            'max.string' => ':Attribute maksimal :max karakter.',
            'between' => ':Attribute harus berada antara :min dan :max.',
            'exists' => ':Attribute yang dipilih tidak ditemukan pada data wilayah.',
            'unique' => 'Data KRS untuk kecamatan dan tahun tersebut sudah tersedia.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'kode_kecamatan' => 'Kecamatan',
            'tahun' => 'Tahun',
            'jumlah_keluarga' => 'Jumlah keluarga',
            'jumlah_keluarga_sasaran' => 'Jumlah keluarga sasaran',
            'kesejahteraan_1' => 'Peringkat kesejahteraan 1',
            'kesejahteraan_2' => 'Peringkat kesejahteraan 2',
            'kesejahteraan_3' => 'Peringkat kesejahteraan 3',
            'kesejahteraan_4' => 'Peringkat kesejahteraan 4',
            'kesejahteraan_lebih_4' => 'Peringkat kesejahteraan lebih dari 4',
            'total_krs' => 'Total KRS',
            'tidak_berisiko' => 'Keluarga tidak berisiko',
            'baduta' => 'Baduta',
            'balita' => 'Balita',
            'pus' => 'PUS',
            'pus_hamil' => 'PUS hamil',
            'air_minum_tidak_layak' => 'Air minum tidak layak',
            'jamban_tidak_layak' => 'Jamban tidak layak',
            'terlalu_muda' => 'Terlalu muda',
            'terlalu_tua' => 'Terlalu tua',
            'terlalu_dekat' => 'Terlalu dekat',
            'terlalu_banyak' => 'Terlalu banyak',
            'jumlah_4t' => 'Jumlah PUS 4 Terlalu',
            'is_simulasi' => 'Status data simulasi',
            'sumber_data' => 'Sumber data',
            'catatan_data' => 'Catatan data',
        ];
    }
}
