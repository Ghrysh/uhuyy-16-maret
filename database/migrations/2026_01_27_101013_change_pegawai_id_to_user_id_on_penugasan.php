<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * 1. Ubah relasi pegawai_id -> user_id
         */
        Schema::table('penugasan', function (Blueprint $table) {
            // drop FK & column lama
            $table->dropForeign(['pegawai_id']);
            $table->dropColumn('pegawai_id');
        });

        Schema::table('penugasan', function (Blueprint $table) {
            // tambah user_id
            $table->foreignUuid('user_id')
                ->after('id')
                ->constrained('users')
                ->cascadeOnDelete();
        });

        /**
         * 2. Hapus tabel pegawai
         */
        Schema::dropIfExists('pegawai');
    }

    public function down(): void
    {
        /**
         * 1. Buat ulang tabel pegawai (minimal)
         */
        Schema::create('pegawai', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nip', 30)->unique();
            $table->string('nama_lengkap', 255);
            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * 2. Kembalikan pegawai_id
         */
        Schema::table('penugasan', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('penugasan', function (Blueprint $table) {
            $table->foreignUuid('pegawai_id')
                ->after('id')
                ->constrained('pegawai')
                ->cascadeOnDelete();
        });
    }
};
