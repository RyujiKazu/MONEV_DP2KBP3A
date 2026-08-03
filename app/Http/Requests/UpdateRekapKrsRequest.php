<?php

namespace App\Http\Requests;

use App\Models\RekapKrs;
use Illuminate\Validation\Rule;

class UpdateRekapKrsRequest extends BaseRekapKrsRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rekapKrs = $this->route('rekapKrs');
        $id = $rekapKrs instanceof RekapKrs ? $rekapKrs->getKey() : $rekapKrs;
        $rules = $this->baseRules();
        $rules['kode_kecamatan'][] = Rule::unique('tb_rekap_krs', 'kode_kecamatan')
            ->where(fn ($query) => $query->where('tahun', $this->input('tahun')))
            ->ignore($id, 'id_rekap');

        return $rules;
    }
}
