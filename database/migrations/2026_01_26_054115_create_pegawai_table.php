<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('nip', 30)->unique();
            $table->string('nama_lengkap', 255);
            $table->string('email', 255)->nullable();
            $table->foreignUuid('wilayah_id')->nullable()->constrained('wilayah')->nullOnDelete();
            $table->foreignUuid('satker_id')->nullable()->constrained('satker')->nullOnDelete();
            $table->foreignUuid('jabatan_aktif_id')->nullable()->constrained('jabatan')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};