<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_kelurahan', function (Blueprint $table) {
            $table->string('kode_kelurahan', 20)->primary();
            $table->string('kode_kecamatan', 20);
            $table->string('nama_kelurahan', 100);
            $table->timestamps();

            $table->foreign('kode_kecamatan')
                ->references('kode_kecamatan')
                ->on('tb_kecamatan')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_kelurahan');
    }
};
