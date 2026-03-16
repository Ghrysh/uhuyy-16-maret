<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_roles', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('nama', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_roles');
    }
};