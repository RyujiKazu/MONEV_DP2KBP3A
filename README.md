# Sistem Monitoring dan Evaluasi Keluarga Berisiko Stunting

Aplikasi ini mendukung monitoring dan evaluasi keluarga berisiko stunting (KRS) menggunakan performance dashboard di DP2KBP3A Kabupaten Subang. Data direkap pada tingkat kecamatan, kemudian dianalisis menjadi empat KPI, tren antarperiode, status evaluasi, tingkat prioritas, faktor risiko dominan, dan rekomendasi awal.

Rekomendasi aplikasi merupakan informasi pendukung. Hasilnya tidak menggantikan verifikasi dokumen sumber maupun keputusan Kepala Bidang PKK.

## Teknologi

- PHP 8.2 atau lebih baru dan Laravel 12
- MySQL untuk lingkungan aplikasi
- SQLite in-memory untuk automated test
- Blade, Tailwind CSS 4, Vite 7
- Chart.js 4 untuk visualisasi dashboard
- `barryvdh/laravel-dompdf` 3.1.1 untuk ekspor PDF

## Persyaratan

- PHP 8.2+ dengan ekstensi PDO, Mbstring, DOM, Fileinfo, OpenSSL, dan Intl
- Composer 2
- Node.js 20.19+ atau 22.12+ dan npm
- MySQL 8.x atau MariaDB yang kompatibel

Ekstensi GD disarankan untuk pemrosesan gambar DomPDF yang lebih lengkap, tetapi laporan tabel tanpa gambar tetap dapat dibuat tanpa GD.

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Pada Windows PowerShell, pengganti perintah salin adalah:

```powershell
Copy-Item .env.example .env
```

Atur koneksi database dan akun awal pada `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dp2kbp3a_monev
DB_USERNAME=root
DB_PASSWORD=

MONEV_SEED_ADMIN_NAME="Admin DP2KBP3A"
MONEV_SEED_ADMIN_USERNAME=admin_dp2kbp3a
MONEV_SEED_ADMIN_PASSWORD="ganti-dengan-password-kuat"
MONEV_SEED_PKK_NAME="PKK DP2KBP3A"
MONEV_SEED_PKK_USERNAME=pkk_dp2kbp3a
MONEV_SEED_PKK_PASSWORD="ganti-dengan-password-kuat-yang-berbeda"
```

Password tidak memiliki nilai bawaan di repository. `DatabaseSeeder` hanya membuat akun yang nama, username, dan password-nya telah dikonfigurasi.

Lanjutkan instalasi:

```bash
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`. Ubah password demo sebelum aplikasi dipakai di lingkungan nyata.

## Master Wilayah

Default seeder tidak membuat kecamatan atau kelurahan agar kode/nama sintetis tidak dianggap sebagai data resmi. Admin dapat memasukkan master wilayah yang sudah diverifikasi melalui menu **Data Wilayah**.

Untuk instalasi dengan master kecamatan kosong atau masih memakai mapping legacy 13 kecamatan dari versi awal aplikasi, jalankan seeder master Kabupaten Subang:

```bash
php artisan db:seed --class=KecamatanSubangMasterSeeder
```

`KecamatanSubangMasterSeeder` menormalkan mapping lama secara transaksional menjadi tepat 30 kecamatan dengan kode resmi. Referensi kelurahan dan rekap KRS yang sudah ada dipindahkan ke mapping resmi di dalam transaksi yang sama, sehingga relasi tidak dihapus atau dibiarkan menunjuk kode lama. Jika master sudah berisi 30 kode resmi, langkah ini tidak perlu diulang.

Kecamatan yang sudah memiliki data KRS tidak dapat dihapus. Kelurahan tetap dipertahankan sebagai master wilayah, tetapi rekap KRS hanya berelasi dengan kecamatan.

## Role dan Hak Akses

| Modul | Admin | PKK |
|---|:---:|:---:|
| Dashboard | Ya | Ya |
| Laporan evaluasi, CSV, PDF | Ya | Ya |
| Data pengguna | Ya | Tidak |
| Data wilayah | Ya | Tidak |
| Data KRS | Ya | Tidak |
| Target indikator | Ya | Tidak |

Permintaan ke halaman administratif oleh PKK menghasilkan HTTP 403. Login dibatasi lima kegagalan per kombinasi username dan alamat IP selama 60 detik.

## Modul

- Autentikasi username/password dan logout
- CRUD pengguna Admin dan PKK
- CRUD master kecamatan dan kelurahan
- CRUD rekap KRS dengan pencarian, filter, urutan, pagination, dan validasi lintas-field
- CRUD target KPI per tahun
- Dashboard performance dengan filter tahun/kecamatan dan Chart.js
- Evaluasi antarperiode, ranking kecamatan, faktor dominan, dan rekomendasi awal
- Laporan web print-friendly serta ekspor CSV dan PDF

Tidak tersedia fitur impor spreadsheet. Data KRS dimasukkan langsung melalui formulir aplikasi.

## Data Awal Penelitian

Data awal penelitian dimuat melalui seeder khusus `Krs2024And2025Seeder` setelah master 30 kecamatan Kabupaten Subang tersedia dan namanya telah diverifikasi.

- Tahun 2025 berisi 30 rekap kecamatan dari dokumen **Rekapitulasi Keluarga Berisiko Stunting Berdasarkan Wilayah Kabupaten Subang Tahun 2025**. Data ini ditandai sebagai data aktual (`is_simulasi = false`).
- Tahun 2024 berisi 30 rekap simulasi deterministik yang dibentuk dari struktur data 2025 untuk menguji perbandingan antarperiode, tren, pemeringkatan, prioritas, faktor dominan, dan rekomendasi. Data ini ditandai `is_simulasi = true` dan selalu menghasilkan nilai yang sama setiap kali seeder dijalankan.
- `sumber_data` menyimpan sumber/provenance, sedangkan `catatan_data` menjelaskan tujuan atau keterbatasan data. Status dan sumber ditampilkan pada Data KRS, dashboard, laporan web, cetak, CSV, dan PDF.
- Data simulasi 2024 bukan data aktual/resmi dan harus diganti setelah data resmi 2024 tersedia.
- `jumlah_4t` dibiarkan `null` untuk kedua periode karena nilai total unik PUS 4T tidak terbaca pada dokumen sumber yang tersedia. Akibatnya KPI-04 ditampilkan sebagai **Data Tidak Tersedia** dan tidak masuk pembagi skor prioritas.
- Subkategori `terlalu_muda`, `terlalu_tua`, `terlalu_dekat`, dan `terlalu_banyak` tetap tersedia untuk grafik. Keempatnya dapat saling beririsan dan tidak boleh dijumlahkan untuk menghasilkan `jumlah_4t`.

Seeder bersifat idempotent dan memakai `updateOrCreate()` berdasarkan kecamatan dan tahun. Jalankan tanpa menghapus data lain:

```bash
php artisan migrate
php artisan db:seed --class=KecamatanSubangMasterSeeder
php artisan db:seed --class=Krs2024And2025Seeder
```

Perintah `KecamatanSubangMasterSeeder` pada urutan di atas diperlukan bila master masih kosong atau sama dengan mapping legacy 13 kecamatan yang didukung; lewati jika 30 kode resminya sudah benar. Keadaan master lain sengaja ditolak agar data wilayah yang tidak dikenal tidak diubah otomatis. Jalankan master seeder sebelum `Krs2024And2025Seeder` agar pemetaan nama CSV memakai kode resmi.

Jangan gunakan `migrate:fresh` pada database pengguna. Jika ada nama kecamatan yang tidak dapat dipetakan, seeder khusus membatalkan seluruh transaksi dan menampilkan daftar yang perlu diperbaiki. `DatabaseSeeder` hanya memanggil seeder KRS pada environment `local` dan `testing` ketika tepat 30 master kecamatan telah tersedia; jika belum, proses instalasi tetap berlanjut dengan peringatan dan data KRS dapat dimuat kemudian secara manual. Data simulasi tidak pernah dimuat otomatis pada `production`.

## Rumus KPI

Seluruh KPI utama menggunakan arah **Minimize**.

| Kode | Indikator | Rumus |
|---|---|---|
| KPI-01 | Persentase Keluarga Berisiko Stunting | `total_krs / jumlah_keluarga_sasaran × 100` |
| KPI-02 | Persentase KRS Tanpa Air Minum Layak | `air_minum_tidak_layak / total_krs × 100` |
| KPI-03 | Persentase KRS Tanpa Jamban Layak | `jamban_tidak_layak / total_krs × 100` |
| KPI-04 | Persentase PUS 4 Terlalu | `jumlah_4t / pus × 100` |

Jika penyebut nol, nilai KPI adalah `null` dan ditampilkan sebagai **Data Tidak Tersedia**, bukan 0%. Agregat kabupaten dihitung dari jumlah seluruh pembilang dibagi jumlah seluruh penyebut; persentase kecamatan tidak dirata-ratakan.

`Baduta`, `Balita`, `PUS`, dan `PUS hamil` dapat beririsan sehingga tidak dijumlahkan sebagai total sasaran. Empat subkategori 4T juga dapat beririsan sehingga tidak dijumlahkan untuk membentuk `jumlah_4t`; sistem hanya memakai nilai total unik dari dokumen sumber. Jika pembilang `jumlah_4t` belum tersedia (`null`), KPI-04 juga `null` meskipun nilai PUS dan subkategorinya tersedia.

## Tren, Tolok Ukur, dan Status

Perubahan dihitung dalam poin persentase:

```text
delta = KPI tahun berjalan - KPI tahun sebelumnya
```

- `delta < 0`: Membaik
- `delta = 0`: Tetap
- `delta > 0`: Memburuk
- data sebelumnya tidak tersedia: Data Pembanding Belum Tersedia

Tolok ukur dipilih dari target aktif untuk KPI dan tahun yang sama. Jika tidak ada target aktif, sistem memakai agregat Kabupaten Subang pada tahun tersebut sebagai tolok ukur internal.

| Posisi aktual | Tren | Status | Skor |
|---|---|---|---:|
| Memenuhi tolok ukur | Membaik/Tetap | Terkendali | 1 |
| Belum memenuhi | Membaik/Tetap | Perlu Perhatian | 2 |
| Memenuhi tolok ukur | Memburuk | Perlu Perhatian | 2 |
| Belum memenuhi | Memburuk | Prioritas | 3 |

Tanpa data pembanding, nilai yang memenuhi tolok ukur tetap **Terkendali**; nilai yang belum memenuhi menjadi **Perlu Perhatian** dan tidak otomatis merah.

Skor prioritas adalah rata-rata skor KPI valid. KPI dengan penyebut nol tidak menjadi pembagi:

- `1,00` sampai `< 1,67`: Prioritas Rendah
- `1,67` sampai `< 2,34`: Prioritas Sedang
- `2,34` sampai `3,00`: Prioritas Tinggi

Klasifikasi tersebut merupakan aturan analitis aplikasi, bukan klasifikasi resmi BKKBN.

## Seeder Demo Opsional

`DemoKrsSeeder` berbeda dari seeder data awal penelitian. Seeder demo ini hanya untuk pengembangan lokal, memakai kecamatan yang sudah ada, tidak menimpa kombinasi kecamatan/tahun yang tersedia, dan tidak pernah dipanggil oleh `DatabaseSeeder`.

```bash
php artisan db:seed --class=DemoKrsSeeder
```

Seeder akan ditolak pada environment `production`. Seluruh hasilnya adalah data simulasi, bukan data aktual atau resmi DP2KBP3A.

## Menjalankan Pemeriksaan

```bash
php artisan test
./vendor/bin/pint --test
npm run build
composer audit
npm audit
```

Pada Windows:

```powershell
vendor\bin\pint --test
npm.cmd run build
```

Test menggunakan SQLite `:memory:` dan tidak bergantung pada database produksi.

## Dokumentasi Teknis

Rincian tabel, relasi, alur evaluasi, route, hak akses, dan keputusan implementasi tersedia di [docs/IMPLEMENTATION.md](docs/IMPLEMENTATION.md).
