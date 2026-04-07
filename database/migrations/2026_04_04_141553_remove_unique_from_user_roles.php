<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_roles', function (Blueprint $table) {
            // Menghapus aturan "unique" pada kolom user_id
            $table->dropUnique('user_roles_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_roles', function (Blueprint $table) {
            // Mengembalikan aturan unique jika di-rollback
            $table->unique('user_id');
        });
    }
};