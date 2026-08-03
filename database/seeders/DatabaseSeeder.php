<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $accounts = [
            'admin' => User::ROLE_ADMIN,
            'pkk' => User::ROLE_PKK,
        ];

        foreach ($accounts as $key => $role) {
            $settings = config("monev.seed_accounts.{$key}", []);
            $name = trim((string) ($settings['name'] ?? ''));
            $username = trim((string) ($settings['username'] ?? ''));
            $password = (string) ($settings['password'] ?? '');

            if ($name === '' || $username === '' || $password === '') {
                $this->command?->warn("Akun {$role} tidak dibuat. Lengkapi konfigurasi MONEV_SEED_".strtoupper($key).'_NAME, _USERNAME, dan _PASSWORD.');

                continue;
            }

            $user = User::query()->firstOrNew(['username' => $username]);
            $user->fill([
                'nama_lengkap' => $name,
                'password' => $password,
                'role' => $role,
            ]);

            if (! $user->exists) {
                $user->created_at = now();
            }

            $user->save();
        }

        if (app()->environment(['local', 'testing'])) {
            $masterMapping = Kecamatan::query()
                ->orderBy('kode_kecamatan')
                ->pluck('nama_kecamatan', 'kode_kecamatan')
                ->map(fn ($name): string => (string) $name)
                ->all();
            $masterCount = count($masterMapping);

            if ($masterMapping === KecamatanSubangMasterSeeder::OFFICIAL_KECAMATAN) {
                $this->call(Krs2024And2025Seeder::class);
            } else {
                $this->command?->warn(
                    "Data KRS 2024/2025 belum dimuat karena mapping {$masterCount} kecamatan belum sama dengan mapping resmi. "
                    .'Jalankan KecamatanSubangMasterSeeder, lalu Krs2024And2025Seeder.',
                );
            }
        }
    }
}
