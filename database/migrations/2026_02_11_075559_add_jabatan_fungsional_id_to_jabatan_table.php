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
        Schema::table('jabatan', function (Blueprint $table) {
            // Menambahkan kolom jabatan_fungsional_id
            $table->foreignUuid('jabatan_fungsional_id')
                ->nullable() // nullable karena mungkin tidak semua jabatan bersifat fungsional
                ->after('jenis_satker_id') // posisi kolom
                ->constrained('jabatan_fungsionals') // relasi ke tabel tujuan
                ->onDelete('set null'); // jika data fungsional dihapus, kolom ini jadi null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatan', function (Blueprint $table) {
            // Menghapus constraint dan kolom
            $table->dropConstrainedForeignId('jabatan_fungsional_id');
        });
    }
};