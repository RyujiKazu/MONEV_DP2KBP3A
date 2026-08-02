<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    use HasFactory;

    protected $table = 'tb_kelurahan';

    protected $primaryKey = 'kode_kelurahan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'kode_kelurahan',
        'kode_kecamatan',
        'nama_kelurahan',
    ];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kode_kecamatan', 'kode_kecamatan');
    }
}