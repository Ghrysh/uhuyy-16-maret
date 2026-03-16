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
        Schema::create('jabatan_fungsionals', function (Blueprint $table) {
            // Menggunakan UUID sebagai Primary Key
            $table->uuid('id')->primary();
            
            // Kolom Kode (Unik untuk mencegah duplikasi)
            $table->string('kode')->unique();
            
            // Kolom Nama Jabatan
            $table->string('name');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jabatan_fungsionals');
    }
};