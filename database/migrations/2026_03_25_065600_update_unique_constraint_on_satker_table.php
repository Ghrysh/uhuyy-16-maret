<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('satker', function (Blueprint $table) {

            $table->dropUnique('satker_kode_satker_unique');
            
            $table->unique(['kode_satker', 'periode_id'], 'satker_kode_periode_unique');
        });
    }

    public function down(): void
    {
        Schema::table('satker', function (Blueprint $table) {

            $table->dropUnique('satker_kode_periode_unique');
            $table->unique('kode_satker', 'satker_kode_satker_unique');
        });
    }
};