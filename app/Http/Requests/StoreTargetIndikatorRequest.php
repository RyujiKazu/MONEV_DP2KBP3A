<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreTargetIndikatorRequest extends BaseTargetIndikatorRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = $this->baseRules();
        $rules['kode_indikator'][] = Rule::unique('tb_target_indikator', 'kode_indikator')
            ->where(fn ($query) => $query->where('tahun_berlaku', $this->input('tahun_berlaku')));

        return $rules;
    }
}
