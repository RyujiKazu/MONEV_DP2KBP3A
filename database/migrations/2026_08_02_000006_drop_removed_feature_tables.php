<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sengaja tidak melakukan apa pun. Menghapus tabel fitur lama secara
        // otomatis berisiko memusnahkan data produksi yang belum diarsipkan.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada perubahan skema yang perlu dibatalkan.
    }
};
