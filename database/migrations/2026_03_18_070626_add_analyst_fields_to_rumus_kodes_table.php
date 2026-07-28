<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
    {
        Schema::table('rumus_kodes', function (Blueprint $table) {
            $table->foreignId('tingkat_wilayah_id')->nullable()->constrained('m_tingkat_wilayah')->nullOnDelete();
            $table->string('kode_awalan')->nullable();
            $table->boolean('is_auto_number')->default(true);
            $table->integer('digit_auto_number')->nullable();
            $table->string('default_nama_satker')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rumus_kodes', function (Blueprint $table) {
            $table->dropForeign(['tingkat_wilayah_id']);
            $table->dropColumn(['tingkat_wilayah_id', 'kode_awalan', 'is_auto_number', 'digit_auto_number', 'default_nama_satker']);
        });
    }
};
