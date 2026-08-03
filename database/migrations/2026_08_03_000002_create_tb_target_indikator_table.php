<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_target_indikator', function (Blueprint $table) {
            $table->bigIncrements('id_target');
            $table->string('kode_indikator', 20);
            $table->string('nama_indikator', 150);
            $table->unsignedSmallInteger('tahun_berlaku');
            $table->decimal('nilai_target', 10, 4);
            $table->enum('arah_target', ['Minimize', 'Maximize']);
            $table->enum('jenis_target', ['Regulatif', 'Internal']);
            $table->string('sumber_target', 255)->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            $table->unique(['kode_indikator', 'tahun_berlaku'], 'target_indikator_kode_tahun_unique');
            $table->index(['tahun_berlaku', 'status_aktif'], 'target_indikator_tahun_aktif_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_target_indikator');
    }
};
