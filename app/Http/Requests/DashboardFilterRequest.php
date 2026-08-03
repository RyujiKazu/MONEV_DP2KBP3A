<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Admin', 'PKK') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'tahun' => ['nullable', 'integer', 'between:2000,'.(now()->year + 1)],
            'kode_kecamatan' => ['nullable', 'string', 'max:20', 'exists:tb_kecamatan,kode_kecamatan'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'tahun.integer' => 'Tahun harus berupa bilangan bulat.',
            'tahun.between' => 'Tahun harus berada antara :min dan :max.',
            'kode_kecamatan.exists' => 'Kecamatan yang dipilih tidak ditemukan.',
        ];
    }
}
