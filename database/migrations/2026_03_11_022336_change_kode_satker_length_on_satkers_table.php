<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('satker', function (Blueprint $table) {
            $table->string('kode_satker', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('satker', function (Blueprint $table) {
            $table->string('kode_satker', 20)->change();
        });
    }
};