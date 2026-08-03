<?php

namespace App\Models;

use Database\Factories\TargetIndikatorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetIndikator extends Model
{
    /** @use HasFactory<TargetIndikatorFactory> */
    use HasFactory;

    public const INDIKATOR = [
        'KPI-01' => 'Persentase Keluarga Berisiko Stunting',
        'KPI-02' => 'Persentase KRS Tanpa Air Minum Layak',
        'KPI-03' => 'Persentase KRS Tanpa Jamban Layak',
        'KPI-04' => 'Persentase PUS 4 Terlalu',
    ];

    protected $table = 'tb_target_indikator';

    protected $primaryKey = 'id_target';

    protected $fillable = [
        'kode_indikator',
        'nama_indikator',
        'tahun_berlaku',
        'nilai_target',
        'arah_target',
        'jenis_target',
        'sumber_target',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'tahun_berlaku' => 'integer',
            'nilai_target' => 'decimal:4',
            'status_aktif' => 'boolean',
        ];
    }
}
