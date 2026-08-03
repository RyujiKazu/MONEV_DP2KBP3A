<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('nama_tabel')->delete();

        DB::table('nama_tabel')->insert([
            [
                'nama' => 'Data Pertama',
                'jumlah' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Data Kedua',
                'jumlah' => 200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
