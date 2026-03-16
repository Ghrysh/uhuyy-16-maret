<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * 1. Tambahkan relasi ke m_jenis_satker
         */
        Schema::table('jabatan', function (Blueprint $table) {
            $table->foreignId('jenis_satker_id')
                ->nullable()
                ->after('jenis_jabatan_id')
                ->constrained('m_jenis_satker');
        });

        /**
         * 2. Hapus foreign key & kolom eselon_level_id
         */
        Schema::table('jabatan', function (Blueprint $table) {
            $table->dropForeign(['eselon_level_id']);
            $table->dropColumn('eselon_level_id');
        });

        /**
         * 3. Hapus tabel m_eselon_level
         */
        Schema::dropIfExists('m_eselon_level');
    }

    public function down(): void
    {
        /**
         * 1. Buat ulang tabel m_eselon_level
         */
        Schema::create('m_eselon_level', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
        });

        /**
         * 2. Tambahkan kembali kolom eselon_level_id ke jabatan
         */
        Schema::table('jabatan', function (Blueprint $table) {
            $table->foreignId('eselon_level_id')
                ->nullable()
                ->after('jenis_jabatan_id')
                ->constrained('m_eselon_level');
        });

        /**
         * 3. Hapus relasi jenis_satker_id
         */
        Schema::table('jabatan', function (Blueprint $table) {
            $table->dropForeign(['jenis_satker_id']);
            $table->dropColumn('jenis_satker_id');
        });
    }
};
