<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penugasan', function (Blueprint $table) {
            $table->uuid('jabatan_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('penugasan', function (Blueprint $table) {
            $table->uuid('jabatan_id')->nullable(false)->change();
        });
    }
};
