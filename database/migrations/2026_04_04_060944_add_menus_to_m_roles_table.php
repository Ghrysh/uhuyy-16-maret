<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_roles', function (Blueprint $table) {
            $table->json('menus')->nullable()->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('m_roles', function (Blueprint $table) {
            $table->dropColumn('menus');
        });
    }
};