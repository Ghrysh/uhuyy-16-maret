<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('distribusi_kuotas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('satker_id');
            $table->uuid('jabatan_fungsional_id');
            
            $table->integer('kuota_pertama')->default(0);
            $table->integer('kuota_muda')->default(0);
            $table->integer('kuota_madya')->default(0);
            $table->integer('kuota_utama')->default(0);
            
            $table->timestamps();

            $table->foreign('satker_id')->references('id')->on('satker')->onDelete('cascade');
            $table->foreign('jabatan_fungsional_id')->references('id')->on('jabatan_fungsionals')->onDelete('cascade');
            
            $table->unique(['satker_id', 'jabatan_fungsional_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('distribusi_kuotas');
    }
};
