# Dokumentasi Implementasi

## Ikhtisar Arsitektur

Aplikasi mengikuti alur server-rendered Laravel. Controller menangani HTTP dan filter, service menangani transformasi/perhitungan, Eloquent menangani persistensi, dan Blade menampilkan hasil. Nilai KPI, status, skor, ranking, faktor dominan, serta rekomendasi tidak disimpan permanen agar selalu konsisten dengan data rekap dan target aktif.

```text
tb_rekap_krs + tb_target_indikator
              |
              v
      KrsEvaluationService
       |        |        |
       v        v        v
  Dashboard   Laporan   CSV/PDF
```

Service utama:

- `KrsEvaluationService`: KPI, agregat tertimbang, tren, benchmark, status, prioritas, faktor dominan, dan ranking.
- `KrsRecommendationService`: rekomendasi awal berdasarkan KPI/faktor/tren.
- `KrsDashboardService`: menyiapkan dataset Chart.js tanpa data statis.
- `KrsReportService`: filter laporan, metadata generate, nama file, dan baris CSV.

## Struktur Tabel

### `users`

Primary key `id_user` bertipe unsigned integer. Kolom utama: `nama_lengkap`, `username` unik, password ter-hash, dan enum role `Admin`/`PKK`.

### `tb_kecamatan`

Primary key string `kode_kecamatan`. Kecamatan memiliki banyak kelurahan dan banyak rekap KRS.

### `tb_kelurahan`

Primary key string `kode_kelurahan`, dengan foreign key ke `tb_kecamatan.kode_kecamatan`. Data ini hanya master wilayah dan tidak menjadi dimensi rekap KRS.

### `tb_rekap_krs`

- Primary key: `id_rekap` (unsigned bigint)
- Foreign key: `kode_kecamatan` ke `tb_kecamatan`, `RESTRICT ON DELETE`
- Foreign key nullable: `created_by` ke `users.id_user`, `SET NULL ON DELETE`
- Periode: `tahun`
- Data keluarga: `jumlah_keluarga`, `jumlah_keluarga_sasaran`, `total_krs`, `tidak_berisiko`
- Kesejahteraan: peringkat 1, 2, 3, 4, dan lebih dari 4
- Sasaran: `baduta`, `balita`, `pus`, `pus_hamil`
- Lingkungan: air minum dan jamban tidak layak
- 4T: terlalu muda, tua, dekat, banyak, serta `jumlah_4t` total unik yang nullable
- Provenance: `is_simulasi` boolean berindeks, `sumber_data` nullable, dan `catatan_data` nullable
- Unique: `kode_kecamatan + tahun`
- Index terpisah: `tahun`, `kode_kecamatan`

### `tb_target_indikator`

- Primary key: `id_target` (unsigned bigint)
- Kode: `KPI-01` sampai `KPI-04`
- Tahun dan target decimal empat angka desimal
- Arah: `Minimize`/`Maximize`
- Jenis: `Regulatif`/`Internal`
- Sumber nullable dan status aktif
- Unique: `kode_indikator + tahun_berlaku`

Tidak ada target resmi pada default seeder.

## Relasi

```text
users (1) -------- (n) tb_rekap_krs
                           |
                           n
                           |
                           1
                    tb_kecamatan (1) -------- (n) tb_kelurahan
```

- `Kecamatan::rekapKrs()`
- `Kecamatan::kelurahans()`
- `RekapKrs::kecamatan()`
- `RekapKrs::pembuat()` melalui `created_by`
- `Kelurahan::kecamatan()`

## Validasi Rekap KRS

`StoreRekapKrsRequest` dan `UpdateRekapKrsRequest` menerapkan:

- kecamatan harus ada dan kombinasi kecamatan/tahun unik;
- tahun 2000 sampai tahun sekarang + 1;
- semua angka yang tersedia berupa integer dan tidak negatif;
- `is_simulasi` harus boolean; sumber maksimal 255 karakter dan catatan bersifat opsional;
- keluarga sasaran tidak melebihi jumlah keluarga;
- total KRS tidak melebihi sasaran;
- `total_krs + tidak_berisiko = jumlah_keluarga_sasaran`;
- jumlah lima peringkat kesejahteraan sama dengan total KRS;
- faktor lingkungan tidak melebihi total KRS;
- `jumlah_4t` boleh kosong jika nilai total unik tidak tersedia; jika diisi, nilainya tidak boleh melebihi PUS.

Ketidakkonsistenan menghasilkan pesan Bahasa Indonesia dan tidak diperbaiki otomatis.

## Alur Perhitungan

1. Ambil seluruh rekap tahun terpilih dan tahun sebelumnya dalam query terpisah.
2. Jumlahkan pembilang/penyebut untuk agregat kabupaten.
3. Ambil seluruh target aktif tahun terpilih dalam satu query.
4. Hitung empat KPI per kecamatan dan kabupaten; penyebut nol atau pembilang `jumlah_4t` yang tidak tersedia menghasilkan `null`.
5. Pilih target aktif, atau agregat kabupaten bila target tidak ada.
6. Hitung delta poin persentase dan status tren.
7. Evaluasi posisi aktual terhadap tolok ukur dan tren menjadi skor 1–3.
8. Rata-ratakan skor indikator valid menjadi skor prioritas; KPI-04 yang `null` tidak masuk pembagi.
9. Pilih faktor dominan dari KPI-02 sampai KPI-04; tie-break memakai `actual / benchmark`, atau actual langsung saat benchmark nol.
10. Bentuk rekomendasi awal dan ranking. Tie-break ranking: skor prioritas, KPI-01, delta KPI-01, lalu nama kecamatan.

Untuk informasi pendukung kesejahteraan, peringkat 1+2 dianggap dominan ketika jumlahnya lebih dari separuh total KRS. Informasi ini menambah rekomendasi sosial tetapi tidak mengubah skor prioritas.

## Route Aplikasi

| Method | URI | Nama | Akses |
|---|---|---|---|
| GET/POST | `/login` | `login`, `login.submit` | Tamu |
| POST | `/logout` | `logout` | Login |
| GET | `/dashboard` | `dashboard.index` | Admin, PKK |
| GET | `/laporan-evaluasi` | `laporan.index` | Admin, PKK |
| GET | `/laporan-evaluasi/cetak` | `laporan.print` | Admin, PKK |
| GET | `/laporan-evaluasi/csv` | `laporan.csv` | Admin, PKK |
| GET | `/laporan-evaluasi/pdf` | `laporan.pdf` | Admin, PKK |
| Resource | `/admin/data-krs` | `admin.rekap-krs.*` | Admin |
| Resource | `/admin/target-indikator` | `admin.target-indikator.*` | Admin |
| CRUD | `/admin/pengguna` | `admin.users.*` | Admin |
| CRUD | `/admin/data-wilayah/*` | `admin.data-wilayah.*` | Admin |

Middleware `EnsureUserHasRole` didaftarkan sebagai alias `role` pada `bootstrap/app.php`. Sidebar menyaring menu untuk pengalaman pengguna, tetapi middleware tetap menjadi kontrol keamanan utama.

## Dashboard dan Laporan

- Dashboard default memakai tahun terbaru yang memiliki rekap.
- Filter kecamatan tidak mengubah benchmark agregat kabupaten.
- Chart.js dimuat melalui npm/Vite dan hanya diinisialisasi ketika halaman memiliki data.
- Nilai `null` dipertahankan di dataset, bukan diubah menjadi nol.
- Status Aktual/Simulasi/Campuran dan sumber data dibawa oleh service evaluasi untuk periode berjalan serta pembanding.
- Jika pembanding mengandung data simulasi, dashboard dan seluruh format laporan menampilkan disclaimer yang eksplisit.
- Grafik 4T tetap memakai empat subkategori mentah meskipun KPI-04 tidak tersedia; subkategori tidak pernah dijumlahkan sebagai total PUS unik.
- Laporan web memiliki tampilan cetak; view PDF memakai CSS sederhana yang didukung DomPDF.
- CSV memakai UTF-8 BOM dan delimiter titik koma agar mudah dibuka pada Excel ber-locale Indonesia.
- Semua keluaran laporan menyertakan tanggal generate, pengguna pembuat, dan filter.

Nama file ekspor:

```text
laporan-evaluasi-krs-{tahun}-{YYYYMMDD}.csv
laporan-evaluasi-krs-{tahun}-{YYYYMMDD}.pdf
```

## Data Awal Penelitian

File `database/data/krs_2025.csv` memuat tepat 30 rekap kecamatan tahun 2025 dari dokumen **Rekapitulasi Keluarga Berisiko Stunting Berdasarkan Wilayah Kabupaten Subang Tahun 2025**. `Krs2024And2025Seeder` memetakan nama CSV ke kode pada `tb_kecamatan`, memvalidasi seluruh relasi angka dan agregat sumber, lalu menyimpan data secara transaksional dan idempotent dengan pasangan `kode_kecamatan + tahun`.

`KecamatanSubangMasterSeeder` disediakan untuk database yang master kecamatannya kosong atau masih menggunakan mapping legacy 13 kecamatan dari versi awal aplikasi. Seeder ini menormalkan master secara transaksional menjadi tepat 30 kode resmi Kabupaten Subang. Foreign key pada kelurahan dan rekap KRS yang telah ada dipetakan ulang di dalam transaksi sebelum mapping lama dirapikan; tidak ada relasi yang dihapus atau dibiarkan yatim. Keadaan master selain kosong, mapping legacy yang dikenal, atau mapping resmi lengkap ditolak agar data wilayah yang tidak dikenal tidak diubah otomatis.

Metadata periode:

- 2025: data aktual, `is_simulasi = false`, sumber dokumen 2025, dan catatan sebagai data utama penelitian DP2KBP3A Kabupaten Subang;
- 2024: data simulasi sementara, `is_simulasi = true`, dibuat secara deterministik dari struktur 2025 untuk pengujian antarperiode;
- data simulasi 2024 wajib diganti setelah data resmi tersedia dan tidak boleh ditafsirkan sebagai data resmi.

Simulasi memakai faktor populasi, risiko, lingkungan, dan reproduksi berdasarkan indeks tetap baris CSV. Kesejahteraan dialokasikan dengan metode sisa terbesar agar jumlah lima kategori selalu sama dengan total KRS. Seeder tidak menggunakan angka acak, sehingga hasil kecamatan yang sama stabil pada setiap eksekusi.

`jumlah_4t` disimpan `null` pada 2024 dan 2025 karena nilai total unik PUS 4T tidak tersedia pada dokumen yang digunakan. Subkategori terlalu muda, tua, dekat, dan banyak dapat beririsan: nilainya tetap dipakai pada grafik, tetapi tidak boleh dijumlahkan untuk mengisi `jumlah_4t`. KPI-04 menjadi **Data Tidak Tersedia** dan dikeluarkan dari pembagi skor prioritas.

Seeder dijalankan setelah master tepat 30 kecamatan tersedia:

```bash
php artisan migrate
php artisan db:seed --class=KecamatanSubangMasterSeeder
php artisan db:seed --class=Krs2024And2025Seeder
```

`KecamatanSubangMasterSeeder` hanya perlu dijalankan ketika master kosong atau masih memakai mapping legacy yang didukung; jika tepat 30 kode resmi sudah tersedia, lanjutkan langsung ke seeder KRS. Urutan master lalu KRS wajib dipertahankan pada database yang perlu dinormalisasi agar data penelitian tidak tersambung ke kode lama.

Jangan menjalankan `migrate:fresh` pada database pengguna. `DatabaseSeeder` memanggil seeder penelitian hanya pada environment `local` dan `testing` ketika tepat 30 master kecamatan tersedia. Jika master belum lengkap, pemanggilan otomatis dilewati dengan peringatan sehingga instalasi baru tetap berhasil; jalankan master seeder, kemudian seeder KRS secara manual. Environment `production` tidak menerima data simulasi secara otomatis.

## Seeder Demo

`DatabaseSeeder` tetap membuat akun yang dikonfigurasi melalui `MONEV_SEED_*` dan tidak otomatis menormalkan master wilayah atau membuat target sintetis. Pada environment lokal/testing, seeder penelitian dipanggil setelah tepat 30 master wilayah tersedia; selain itu proses dilewati dengan peringatan. Normalisasi master legacy dilakukan secara eksplisit melalui `KecamatanSubangMasterSeeder`.

`DemoKrsSeeder` terpisah dari data awal penelitian, hanya dapat dijalankan manual di non-production, menggunakan kecamatan yang sudah ada, dan memberi peringatan bahwa hasilnya data demo/simulasi.

Migration lama `2026_08_02_000006_drop_removed_feature_tables.php` dijadikan no-op untuk mencegah penghapusan tabel lama secara otomatis pada instalasi yang belum pernah menjalankannya. Tabel baru selalu dibuat melalui migration baru dan tidak bergantung pada tabel lama.

## Keamanan dan Integritas

- CSRF aktif pada seluruh form web.
- Password memakai cast `hashed` model User.
- Login memiliki rate limiting per username/IP.
- Mass assignment dibatasi melalui `$fillable`.
- Mutasi KRS/target memakai transaksi database.
- Foreign key KRS mencegah penghapusan kecamatan yang sudah dipakai.
- Query dashboard melakukan eager loading dan mengambil target/data pembanding di luar loop.
- Output Blade di-escape secara default; data JavaScript dikirim melalui `Js::from`.

## Verifikasi

Automated test memakai SQLite in-memory melalui `phpunit.xml`. Pemeriksaan rilis:

```bash
php artisan test
vendor/bin/pint --test
npm run build
composer audit
npm audit
```

`php artisan test` memakai SQLite in-memory dan menjalankan migration melalui `RefreshDatabase`. Untuk smoke test MySQL, arahkan seluruh konfigurasi `DB_*` ke database khusus pengujian yang boleh dikosongkan, lalu jalankan `php artisan migrate:fresh --seed`. Jangan pernah menjalankan `migrate:fresh` terhadap database produksi.
