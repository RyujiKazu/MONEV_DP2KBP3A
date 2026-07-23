<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Kelurahan;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'tb_kecamatan';

    protected $primaryKey = 'kode_kecamatan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'kode_kecamatan',
        'nama_kecamatan',
    ];

    public function kelurahans()
    {
        return $this->hasMany(Kelurahan::class, 'kode_kecamatan', 'kode_kecamatan');
    }

    public function evaluasiKrs()
    {
        return $this->hasMany(EvaluasiKrs::class, 'kode_kecamatan', 'kode_kecamatan');
    }
}