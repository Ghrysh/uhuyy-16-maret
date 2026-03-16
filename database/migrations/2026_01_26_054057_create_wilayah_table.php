<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('kode_wilayah', 20)->unique();
            $table->string('nama_wilayah', 255);
            $table->foreignId('tingkat_wilayah_id')->constrained('m_tingkat_wilayah');
            
            // Definisikan kolomnya dulu tanpa constrained()
            $table->uuid('parent_wilayah_id')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Tambahkan foreign key setelah tabel selesai dibuat
        Schema::table('wilayah', function (Blueprint $table) {
            $table->foreign('parent_wilayah_id')
                  ->references('id')
                  ->on('wilayah')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah');
    }
};