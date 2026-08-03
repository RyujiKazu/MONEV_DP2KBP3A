<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_rekap_krs', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_4t')->nullable()->default(null)->change();
            $table->boolean('is_simulasi')
                ->default(false)
                ->after('jumlah_4t')
                ->index('rekap_krs_is_simulasi_index');
            $table->string('sumber_data', 255)->nullable()->after('is_simulasi');
            $table->text('catatan_data')->nullable()->after('sumber_data');
        });
    }

    public function down(): void
    {
        DB::table('tb_rekap_krs')
            ->whereNull('jumlah_4t')
            ->update(['jumlah_4t' => 0]);

        Schema::table('tb_rekap_krs', function (Blueprint $table) {
            $table->dropIndex('rekap_krs_is_simulasi_index');
            $table->dropColumn(['is_simulasi', 'sumber_data', 'catatan_data']);
            $table->unsignedInteger('jumlah_4t')->nullable(false)->default(0)->change();
        });
    }
};
