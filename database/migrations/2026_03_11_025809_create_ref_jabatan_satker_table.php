<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. Buat tabel dan primary key dulu
        Schema::create('ref_jabatan_satker', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Tetap sebagai UUID Primary
            
            // Kolom foreign key tetap dibuat, tapi tanpa constraint dulu
            $table->foreignId('tingkat_wilayah_id')->nullable()
                  ->constrained('m_tingkat_wilayah')->onDelete('cascade');
            
            $table->uuid('parent_id')->nullable(); // Hanya kolom UUID biasa dulu
            
            $table->string('key_jabatan');
            $table->string('label_jabatan');
            $table->string('kode_dasar')->nullable();
            $table->boolean('is_increment')->default(false);
            $table->timestamps();
        });

        // 2. Tambahkan foreign key untuk parent_id secara terpisah
        Schema::table('ref_jabatan_satker', function (Blueprint $table) {
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('ref_jabatan_satker')
                  ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('ref_jabatan_satker');
    }
};