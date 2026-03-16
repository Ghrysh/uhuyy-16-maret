<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satker', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('kode_satker', 20)->unique();
            $table->string('nama_satker', 255);
            $table->foreignId('jenis_satker_id')->constrained('m_jenis_satker');
            $table->foreignUuid('wilayah_id')->nullable()->constrained('wilayah')->nullOnDelete();
            
            // Definisikan kolomnya dulu
            $table->uuid('parent_satker_id')->nullable();
            
            $table->boolean('status_aktif')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Tambahkan foreign key di luar blok create
        Schema::table('satker', function (Blueprint $table) {
            $table->foreign('parent_satker_id')
                  ->references('id')
                  ->on('satker')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satker');
    }
};