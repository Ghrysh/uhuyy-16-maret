<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('penugasan', function (Blueprint $table) {
            $table->date('tanggal_mulai_cuti')->nullable()->after('tanggal_selesai');
            $table->date('tanggal_selesai_cuti')->nullable()->after('tanggal_mulai_cuti');
        });
    }

    public function down()
    {
        Schema::table('penugasan', function (Blueprint $table) {
            $table->dropColumn(['tanggal_mulai_cuti', 'tanggal_selesai_cuti']);
        });
    }
};