<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_roles', function (Blueprint $table) {
            $table->boolean('is_assignable')->default(true)->after('menus');
        });

        Schema::table('m_jenis_penugasan', function (Blueprint $table) {
            $table->json('menus')->nullable()->after('nama');
            $table->boolean('is_assignable')->default(true)->after('menus');
        });
    }

    public function down(): void
    {
        Schema::table('m_roles', function (Blueprint $table) {
            $table->dropColumn('is_assignable');
        });
        Schema::table('m_jenis_penugasan', function (Blueprint $table) {
            $table->dropColumn(['menus', 'is_assignable']);
        });
    }
};