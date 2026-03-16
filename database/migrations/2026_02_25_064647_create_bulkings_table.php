<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulkings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // contoh: update_satker
            $table->uuid('satker_id')->nullable(); // ubah jadi uuid
            $table->uuid('created_by'); // user login
            $table->integer('total_data')->default(0);
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulkings');
    }
};