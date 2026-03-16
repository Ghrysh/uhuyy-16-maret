<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('satker', function (Blueprint $table) {

            $table->uuid('ref_jabatan_satker_id')
                ->nullable()
                ->after('parent_satker_id');

            $table->foreign('ref_jabatan_satker_id')
                ->references('id')
                ->on('ref_jabatan_satker')
                ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('satker', function (Blueprint $table) {

            $table->dropForeign(['ref_jabatan_satker_id']);
            $table->dropColumn('ref_jabatan_satker_id');

        });
    }
};