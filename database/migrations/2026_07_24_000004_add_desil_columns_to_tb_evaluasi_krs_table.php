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
        Schema::table('tb_evaluasi_krs', function (Blueprint $table) {
            $table->integer('desil_1')->default(0)->after('sasaran_pus_hamil');
            $table->integer('desil_2')->default(0)->after('desil_1');
            $table->integer('desil_3')->default(0)->after('desil_2');
            $table->integer('desil_4')->default(0)->after('desil_3');
            $table->integer('desil_5')->default(0)->after('desil_4');
            $table->integer('desil_6')->default(0)->after('desil_5');
            $table->integer('desil_7')->default(0)->after('desil_6');
            $table->integer('desil_8')->default(0)->after('desil_7');
            $table->integer('desil_9')->default(0)->after('desil_8');
            $table->integer('desil_10')->default(0)->after('desil_9');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_evaluasi_krs', function (Blueprint $table) {
            $table->dropColumn([
                'desil_1',
                'desil_2',
                'desil_3',
                'desil_4',
                'desil_5',
                'desil_6',
                'desil_7',
                'desil_8',
                'desil_9',
                'desil_10',
            ]);
        });
    }
};