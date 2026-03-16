<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jabatan', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('kode_jabatan', 50)->unique();
            $table->string('nama_jabatan', 255);
            $table->foreignId('jenis_jabatan_id')->constrained('m_jenis_jabatan');
            $table->foreignId('eselon_level_id')->nullable()->constrained('m_eselon_level');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jabatan');
    }
};