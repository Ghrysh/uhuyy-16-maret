<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('jabatan', function (Blueprint $table) {
            $table->integer('b_pertama')->default(0)->after('baseline');
            $table->integer('b_muda')->default(0)->after('b_pertama');
            $table->integer('b_madya')->default(0)->after('b_muda');
            $table->integer('b_utama')->default(0)->after('b_madya');
        });
    }

    public function down(): void {
        Schema::table('jabatan', function (Blueprint $table) {
            $table->dropColumn(['b_pertama', 'b_muda', 'b_madya', 'b_utama']);
        });
    }
};