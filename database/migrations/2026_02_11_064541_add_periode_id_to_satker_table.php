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
            // Karena tabel periode menggunakan UUID, maka kolom periode_id harus foreignUuid
            $table->foreignUuid('periode_id')
                ->nullable() // nullable jika data lama belum memiliki periode
                ->after('keterangan') // meletakkan kolom setelah keterangan
                ->constrained('periodes') // merujuk ke tabel periodes
                ->onDelete('set null'); // jika periode dihapus, kolom ini jadi null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('satker', function (Blueprint $table) {
            $table->dropConstrainedForeignId('periode_id');
        });
    }
};
