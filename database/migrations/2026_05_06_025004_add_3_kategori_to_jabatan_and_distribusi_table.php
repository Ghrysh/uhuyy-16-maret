<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tambah kolom di tabel jabatan (Untuk Baseline Utama)
        Schema::table('jabatan', function (Blueprint $table) {
            $table->integer('b_pertama_menpan')->default(0);
            $table->integer('b_muda_menpan')->default(0);
            $table->integer('b_madya_menpan')->default(0);
            $table->integer('b_utama_menpan')->default(0);

            $table->integer('b_pertama_eksisting')->default(0);
            $table->integer('b_muda_eksisting')->default(0);
            $table->integer('b_madya_eksisting')->default(0);
            $table->integer('b_utama_eksisting')->default(0);

            $table->integer('b_pertama_lowongan')->default(0);
            $table->integer('b_muda_lowongan')->default(0);
            $table->integer('b_madya_lowongan')->default(0);
            $table->integer('b_utama_lowongan')->default(0);
        });

        // 2. Tambah kolom di tabel distribusi_kuotas (Untuk Input per Satker)
        Schema::table('distribusi_kuotas', function (Blueprint $table) {
            $table->integer('kp_menpan')->default(0);
            $table->integer('kmu_menpan')->default(0);
            $table->integer('kma_menpan')->default(0);
            $table->integer('ku_menpan')->default(0);

            $table->integer('kp_eksisting')->default(0);
            $table->integer('kmu_eksisting')->default(0);
            $table->integer('kma_eksisting')->default(0);
            $table->integer('ku_eksisting')->default(0);

            $table->integer('kp_lowongan')->default(0);
            $table->integer('kmu_lowongan')->default(0);
            $table->integer('kma_lowongan')->default(0);
            $table->integer('ku_lowongan')->default(0);
        });
    }

    public function down()
    {
        Schema::table('jabatan', function (Blueprint $table) {
            $table->dropColumn(['b_pertama_menpan', 'b_muda_menpan', 'b_madya_menpan', 'b_utama_menpan', 'b_pertama_eksisting', 'b_muda_eksisting', 'b_madya_eksisting', 'b_utama_eksisting', 'b_pertama_lowongan', 'b_muda_lowongan', 'b_madya_lowongan', 'b_utama_lowongan']);
        });
        Schema::table('distribusi_kuotas', function (Blueprint $table) {
            $table->dropColumn(['kp_menpan', 'kmu_menpan', 'kma_menpan', 'ku_menpan', 'kp_eksisting', 'kmu_eksisting', 'kma_eksisting', 'ku_eksisting', 'kp_lowongan', 'kmu_lowongan', 'kma_lowongan', 'ku_lowongan']);
        });
    }
};
