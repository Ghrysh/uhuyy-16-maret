<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rumus_kodes', function (Blueprint $table) {
            $table->id();
            $table->string('nama_rumus'); 
            
            $table->foreignId('jenis_satker_id')->nullable()->constrained('m_jenis_satker')->nullOnDelete();

            $table->foreignUuid('ref_jabatan_satker_id')->nullable()->constrained('ref_jabatan_satker')->nullOnDelete();
            
            $table->string('pola'); 
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rumus_kodes');
    }
};