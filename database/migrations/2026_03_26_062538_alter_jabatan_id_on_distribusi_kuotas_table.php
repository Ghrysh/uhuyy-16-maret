<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('distribusi_kuotas', function (Blueprint $table) {
            $table->dropForeign(['jabatan_fungsional_id']);
            $table->dropUnique(['satker_id', 'jabatan_fungsional_id']);
            $table->dropColumn('jabatan_fungsional_id');

            $table->uuid('jabatan_id')->after('satker_id')->nullable();
            $table->foreign('jabatan_id')->references('id')->on('jabatan')->onDelete('cascade');

            $table->unique(['satker_id', 'jabatan_id']);
        });
    }

    public function down(): void {
        Schema::table('distribusi_kuotas', function (Blueprint $table) {
            $table->dropForeign(['jabatan_id']);
            $table->dropUnique(['satker_id', 'jabatan_id']);
            $table->dropColumn('jabatan_id');

            $table->uuid('jabatan_fungsional_id')->after('satker_id')->nullable();
            $table->foreign('jabatan_fungsional_id')->references('id')->on('jabatan_fungsionals')->onDelete('cascade');
            $table->unique(['satker_id', 'jabatan_fungsional_id']);
        });
    }
};