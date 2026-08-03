<?php

namespace App\Http\Requests;

use App\Models\TargetIndikator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class BaseTargetIndikatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'Admin';
    }

    /** @return array<string, mixed> */
    protected function baseRules(): array
    {
        return [
            'kode_indikator' => ['required', Rule::in(array_keys(TargetIndikator::INDIKATOR))],
            'tahun_berlaku' => ['required', 'integer', 'between:2000,'.(now()->year + 1)],
            'nilai_target' => ['required', 'numeric', 'decimal:0,4', 'min:0', 'max:100'],
            'arah_target' => ['required', Rule::in(['Minimize', 'Maximize'])],
            'jenis_target' => ['required', Rule::in(['Regulatif', 'Internal'])],
            'sumber_target' => ['nullable', 'string', 'max:255'],
            'status_aktif' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'integer' => ':Attribute harus berupa bilangan bulat.',
            'numeric' => ':Attribute harus berupa angka.',
            'nilai_target.decimal' => 'Nilai target maksimal memiliki 4 angka di belakang koma.',
            'string' => ':Attribute harus berupa teks.',
            'between' => ':Attribute harus berada antara :min dan :max.',
            'min' => ':Attribute minimal :min.',
            'max' => ':Attribute maksimal :max.',
            'in' => ':Attribute yang dipilih tidak valid.',
            'boolean' => ':Attribute harus berupa pilihan aktif atau nonaktif.',
            'unique' => 'Target untuk indikator dan tahun tersebut sudah tersedia.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'kode_indikator' => 'Kode indikator',
            'tahun_berlaku' => 'Tahun berlaku',
            'nilai_target' => 'Nilai target',
            'arah_target' => 'Arah target',
            'jenis_target' => 'Jenis target',
            'sumber_target' => 'Sumber target',
            'status_aktif' => 'Status aktif',
        ];
    }
}
