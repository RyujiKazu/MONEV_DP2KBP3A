<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_rekap_krs', function (Blueprint $table) {
            $table->bigIncrements('id_rekap');
            $table->string('kode_kecamatan', 20);
            $table->unsignedSmallInteger('tahun');
            $table->unsignedInteger('jumlah_keluarga');
            $table->unsignedInteger('jumlah_keluarga_sasaran');
            $table->unsignedInteger('kesejahteraan_1')->default(0);
            $table->unsignedInteger('kesejahteraan_2')->default(0);
            $table->unsignedInteger('kesejahteraan_3')->default(0);
            $table->unsignedInteger('kesejahteraan_4')->default(0);
            $table->unsignedInteger('kesejahteraan_lebih_4')->default(0);
            $table->unsignedInteger('total_krs');
            $table->unsignedInteger('tidak_berisiko');
            $table->unsignedInteger('baduta')->default(0);
            $table->unsignedInteger('balita')->default(0);
            $table->unsignedInteger('pus')->default(0);
            $table->unsignedInteger('pus_hamil')->default(0);
            $table->unsignedInteger('air_minum_tidak_layak')->default(0);
            $table->unsignedInteger('jamban_tidak_layak')->default(0);
            $table->unsignedInteger('terlalu_muda')->default(0);
            $table->unsignedInteger('terlalu_tua')->default(0);
            $table->unsignedInteger('terlalu_dekat')->default(0);
            $table->unsignedInteger('terlalu_banyak')->default(0);
            $table->unsignedInteger('jumlah_4t')->default(0);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['kode_kecamatan', 'tahun'], 'rekap_krs_kecamatan_tahun_unique');
            $table->index('tahun', 'rekap_krs_tahun_index');
            $table->index('kode_kecamatan', 'rekap_krs_kecamatan_index');

            $table->foreign('kode_kecamatan')
                ->references('kode_kecamatan')
                ->on('tb_kecamatan')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('created_by')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_rekap_krs');
    }
};
