<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreRekapKrsRequest extends BaseRekapKrsRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = $this->baseRules();
        $rules['kode_kecamatan'][] = Rule::unique('tb_rekap_krs', 'kode_kecamatan')
            ->where(fn ($query) => $query->where('tahun', $this->input('tahun')));

        return $rules;
    }
}
