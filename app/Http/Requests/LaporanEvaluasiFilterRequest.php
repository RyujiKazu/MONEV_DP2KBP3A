<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class LaporanEvaluasiFilterRequest extends DashboardFilterRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'tingkat_prioritas' => ['nullable', Rule::in(['Prioritas Rendah', 'Prioritas Sedang', 'Prioritas Tinggi'])],
            'status_tren' => ['nullable', Rule::in(['Membaik', 'Tetap', 'Memburuk', 'Data Pembanding Belum Tersedia', 'Data Tidak Tersedia'])],
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'tingkat_prioritas.in' => 'Tingkat prioritas yang dipilih tidak valid.',
            'status_tren.in' => 'Status tren yang dipilih tidak valid.',
        ]);
    }
}
