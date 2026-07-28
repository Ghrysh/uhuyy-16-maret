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
        // 1. Tambah kolom di tabel 'jabatan'
        Schema::table('jabatan', function (Blueprint $table) {
            // Baseline MENPANRB (Jenjang 5 - 8)
            $table->integer('b_lima_menpan')->default(0);
            $table->integer('b_enam_menpan')->default(0);
            $table->integer('b_tujuh_menpan')->default(0);
            $table->integer('b_delapan_menpan')->default(0);

            // Baseline EKSISTING (Jenjang 5 - 8)
            $table->integer('b_lima_eksisting')->default(0);
            $table->integer('b_enam_eksisting')->default(0);
            $table->integer('b_tujuh_eksisting')->default(0);
            $table->integer('b_delapan_eksisting')->default(0);

            // Baseline LOWONGAN (Jenjang 5 - 8)
            $table->integer('b_lima_lowongan')->default(0);
            $table->integer('b_enam_lowongan')->default(0);
            $table->integer('b_tujuh_lowongan')->default(0);
            $table->integer('b_delapan_lowongan')->default(0);
        });

        // 2. Tambah kolom di tabel 'distribusi_kuotas'
        Schema::table('distribusi_kuotas', function (Blueprint $table) {
            // Kuota MENPANRB (Jenjang 5 - 8)
            $table->integer('k5_menpan')->default(0);
            $table->integer('k6_menpan')->default(0);
            $table->integer('k7_menpan')->default(0);
            $table->integer('k8_menpan')->default(0);

            // Kuota EKSISTING (Jenjang 5 - 8)
            $table->integer('k5_eksisting')->default(0);
            $table->integer('k6_eksisting')->default(0);
            $table->integer('k7_eksisting')->default(0);
            $table->integer('k8_eksisting')->default(0);

            // Kuota LOWONGAN (Jenjang 5 - 8)
            $table->integer('k5_lowongan')->default(0);
            $table->integer('k6_lowongan')->default(0);
            $table->integer('k7_lowongan')->default(0);
            $table->integer('k8_lowongan')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatan', function (Blueprint $table) {
            $table->dropColumn([
                'b_lima_menpan', 'b_enam_menpan', 'b_tujuh_menpan', 'b_delapan_menpan',
                'b_lima_eksisting', 'b_enam_eksisting', 'b_tujuh_eksisting', 'b_delapan_eksisting',
                'b_lima_lowongan', 'b_enam_lowongan', 'b_tujuh_lowongan', 'b_delapan_lowongan'
            ]);
        });

        Schema::table('distribusi_kuotas', function (Blueprint $table) {
            $table->dropColumn([
                'k5_menpan', 'k6_menpan', 'k7_menpan', 'k8_menpan',
                'k5_eksisting', 'k6_eksisting', 'k7_eksisting', 'k8_eksisting',
                'k5_lowongan', 'k6_lowongan', 'k7_lowongan', 'k8_lowongan'
            ]);
        });
    }
};