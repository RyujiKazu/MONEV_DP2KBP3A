<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function kelurahans(): HasMany
    {
        return $this->hasMany(Kelurahan::class, 'kode_kecamatan', 'kode_kecamatan');
    }

    public function rekapKrs(): HasMany
    {
        return $this->hasMany(RekapKrs::class, 'kode_kecamatan', 'kode_kecamatan');
    }
}
