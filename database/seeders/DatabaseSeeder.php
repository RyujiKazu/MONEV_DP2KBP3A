<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['username' => 'admin_dp2kbp3a'],
            [
                'nama_lengkap' => 'Admin DP2KBP3A',
                'password' => Hash::make('admin123'),
                'role' => 'Admin',
                'created_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['username' => 'pkk_dp2kbp3a'],
            [
                'nama_lengkap' => 'PKK DP2KBP3A',
                'password' => Hash::make('pkk123'),
                'role' => 'PKK',
                'created_at' => now(),
            ]
        );

        $kecamatanData = [
            ['kode' => '32.13.01', 'nama' => 'Cibogo'],
            ['kode' => '32.13.02', 'nama' => 'Subang'],
            ['kode' => '32.13.03', 'nama' => 'Kalijati'],
            ['kode' => '32.13.04', 'nama' => 'Pabuaran'],
            ['kode' => '32.13.05', 'nama' => 'Cijambe'],
            ['kode' => '32.13.06', 'nama' => 'Binong'],
            ['kode' => '32.13.07', 'nama' => 'Patokbeusi'],
            ['kode' => '32.13.08', 'nama' => 'Purwadadi'],
            ['kode' => '32.13.09', 'nama' => 'Jalancagak'],
            ['kode' => '32.13.10', 'nama' => 'Legonkulon'],
        ];

        foreach ($kecamatanData as $index => $item) {
            Kecamatan::updateOrCreate(
                ['kode_kecamatan' => $item['kode']],
                ['nama_kecamatan' => $item['nama']]
            );

            Kelurahan::updateOrCreate(
                ['kode_kelurahan' => $item['kode'] . '.01'],
                [
                    'kode_kecamatan' => $item['kode'],
                    'nama_kelurahan' => $item['nama'] . ' Utara',
                ]
            );
        }
    }
}
