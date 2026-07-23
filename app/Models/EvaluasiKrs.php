<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Kecamatan;
use App\Models\Kelurahan;

class EvaluasiKrs extends Model
{
    use HasFactory;

    protected $table = 'tb_evaluasi_krs';

    protected $primaryKey = 'id_rekap';

    protected $fillable = [
        'kode_kecamatan',
        'kode_kelurahan',
        'periode_evaluasi',
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
        'air_tidak_layak',
        'jamban_tidak_layak',
        'terlalu_muda',
        'terlalu_tua',
        'terlalu_dekat',
        'terlalu_banyak',
        'jumlah_terlalu',
    ];

    protected function casts(): array
    {
        return [
            'periode_evaluasi' => 'date',
        ];
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kode_kecamatan', 'kode_kecamatan');
    }

    public function kelurahan()
    {
        return $this->belongsTo(Kelurahan::class, 'kode_kelurahan', 'kode_kelurahan');
    }
}