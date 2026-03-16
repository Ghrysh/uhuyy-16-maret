<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penugasan', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
            $table->foreignUuid('satker_id')->constrained('satker')->cascadeOnDelete();
            $table->foreignUuid('jabatan_id')->constrained('jabatan')->cascadeOnDelete();
            $table->foreignId('jenis_penugasan_id')->constrained('m_jenis_penugasan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penugasan');
    }
};