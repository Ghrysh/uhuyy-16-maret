<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rumus_kodes', function (Blueprint $table) {
            $table->boolean('is_auto_name')->default(false)->after('is_applied');
            $table->string('base_auto_name')->nullable()->after('is_auto_name');
            $table->boolean('is_name_locked')->default(false)->after('base_auto_name');
            $table->json('custom_names_map')->nullable()->after('is_name_locked');
        });
    }

    public function down(): void
    {
        Schema::table('rumus_kodes', function (Blueprint $table) {
            $table->dropColumn(['is_auto_name', 'base_auto_name', 'is_name_locked', 'custom_names_map']);
        });
    }
};