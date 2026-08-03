<?php

namespace App\Http\Requests;

use App\Models\TargetIndikator;
use Illuminate\Validation\Rule;

class UpdateTargetIndikatorRequest extends BaseTargetIndikatorRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $target = $this->route('targetIndikator');
        $id = $target instanceof TargetIndikator ? $target->getKey() : $target;
        $rules = $this->baseRules();
        $rules['kode_indikator'][] = Rule::unique('tb_target_indikator', 'kode_indikator')
            ->where(fn ($query) => $query->where('tahun_berlaku', $this->input('tahun_berlaku')))
            ->ignore($id, 'id_target');

        return $rules;
    }
}
