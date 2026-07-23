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
        Schema::create('tb_evaluasi_krs', function (Blueprint $table) {
            $table->increments('id_rekap');
            $table->string('kode_kecamatan', 20);
            $table->string('kode_kelurahan', 20)->nullable();
            $table->date('periode_evaluasi');

            $table->integer('jumlah_keluarga')->default(0);
            $table->integer('jumlah_keluarga_sasaran')->default(0);

            $table->integer('peringkat_1')->default(0);
            $table->integer('peringkat_2')->default(0);
            $table->integer('peringkat_3')->default(0);
            $table->integer('peringkat_4')->default(0);
            $table->integer('peringkat_lebih_4')->default(0);
            $table->integer('total_berisiko')->default(0);
            $table->integer('tidak_berisiko')->default(0);

            $table->integer('sasaran_baduta')->default(0);
            $table->integer('sasaran_balita')->default(0);
            $table->integer('sasaran_pus')->default(0);
            $table->integer('sasaran_pus_hamil')->default(0);

            $table->integer('air_tidak_layak')->default(0);
            $table->integer('jamban_tidak_layak')->default(0);

            $table->integer('terlalu_muda')->default(0);
            $table->integer('terlalu_tua')->default(0);
            $table->integer('terlalu_dekat')->default(0);
            $table->integer('terlalu_banyak')->default(0);
            $table->integer('jumlah_terlalu')->default(0);

            $table->timestamps();

            $table->foreign('kode_kecamatan')
                ->references('kode_kecamatan')
                ->on('tb_kecamatan')
                ->cascadeOnDelete();

            $table->foreign('kode_kelurahan')
                ->references('kode_kelurahan')
                ->on('tb_kelurahan')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_evaluasi_krs');
    }
};