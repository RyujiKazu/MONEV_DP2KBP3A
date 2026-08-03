<?php

namespace App\Models;

use Database\Factories\RekapKrsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekapKrs extends Model
{
    /** @use HasFactory<RekapKrsFactory> */
    use HasFactory;

    protected $table = 'tb_rekap_krs';

    protected $primaryKey = 'id_rekap';

    protected $fillable = [
        'kode_kecamatan',
        'tahun',
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
        'jumlah_4t',
        'is_simulasi',
        'sumber_data',
        'catatan_data',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'jumlah_keluarga' => 'integer',
            'jumlah_keluarga_sasaran' => 'integer',
            'kesejahteraan_1' => 'integer',
            'kesejahteraan_2' => 'integer',
            'kesejahteraan_3' => 'integer',
            'kesejahteraan_4' => 'integer',
            'kesejahteraan_lebih_4' => 'integer',
            'total_krs' => 'integer',
            'tidak_berisiko' => 'integer',
            'baduta' => 'integer',
            'balita' => 'integer',
            'pus' => 'integer',
            'pus_hamil' => 'integer',
            'air_minum_tidak_layak' => 'integer',
            'jamban_tidak_layak' => 'integer',
            'terlalu_muda' => 'integer',
            'terlalu_tua' => 'integer',
            'terlalu_dekat' => 'integer',
            'terlalu_banyak' => 'integer',
            'jumlah_4t' => 'integer',
            'is_simulasi' => 'boolean',
            'created_by' => 'integer',
        ];
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kode_kecamatan', 'kode_kecamatan');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }
}
