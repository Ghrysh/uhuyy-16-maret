<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('jabatan', function (Blueprint $table) {
            $table->uuid('periode_id')->nullable()->after('id');
            $table->integer('baseline')->default(0)->after('nama_jabatan');
            
            $table->foreign('periode_id')->references('id')->on('periodes')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::table('jabatan', function (Blueprint $table) {
            $table->dropForeign(['periode_id']);
            $table->dropColumn(['periode_id', 'baseline']);
        });
    }
};