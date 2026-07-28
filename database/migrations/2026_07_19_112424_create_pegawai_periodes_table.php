<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pegawai_periodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('satker_id');
            $table->uuid('periode_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('satker_id')->references('id')->on('satker')->onDelete('cascade');
            $table->foreign('periode_id')->references('id')->on('periodes')->onDelete('cascade');

            // Constraint: 1 user hanya bisa ada di 1 satker pada 1 periode
            $table->unique(['user_id', 'periode_id']);
        });

        // Data Migration: Copy existing user -> satker associations to the new pivot table
        // We will loop through users who have satker_id
        $users = DB::table('users')->whereNotNull('satker_id')->get();
        foreach ($users as $user) {
            $satker = DB::table('satker')->where('id', $user->satker_id)->first();
            if ($satker && $satker->periode_id) {
                DB::table('pegawai_periodes')->insert([
                    'id' => Str::uuid(),
                    'user_id' => $user->id,
                    'satker_id' => $satker->id,
                    'periode_id' => $satker->periode_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai_periodes');
    }
};
