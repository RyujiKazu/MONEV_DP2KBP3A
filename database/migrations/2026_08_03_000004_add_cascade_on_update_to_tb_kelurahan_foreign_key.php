<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_kelurahan', function (Blueprint $table) {
            $table->dropForeign(['kode_kecamatan']);
        });

        Schema::table('tb_kelurahan', function (Blueprint $table) {
            $table->foreign('kode_kecamatan')
                ->references('kode_kecamatan')
                ->on('tb_kecamatan')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('tb_kelurahan', function (Blueprint $table) {
            $table->dropForeign(['kode_kecamatan']);
        });

        Schema::table('tb_kelurahan', function (Blueprint $table) {
            $table->foreign('kode_kecamatan')
                ->references('kode_kecamatan')
                ->on('tb_kecamatan')
                ->cascadeOnDelete();
        });
    }
};
