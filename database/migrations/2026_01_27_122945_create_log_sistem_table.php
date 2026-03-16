<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_sistem', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // waktu kejadian
            $table->timestamp('waktu')->useCurrent();

            // aksi: CREATE, UPDATE, DELETE, LOGIN, LOGOUT, dll
            $table->string('aksi', 50);

            // nama tabel yang terdampak
            $table->string('nama_tabel', 100);

            // id data pada tabel (uuid / varchar)
            $table->string('data_id')->nullable();

            // catatan perubahan
            $table->text('perubahan')->nullable();

            // user pelaku
            $table->uuid('user_id')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_sistem');
    }
};
