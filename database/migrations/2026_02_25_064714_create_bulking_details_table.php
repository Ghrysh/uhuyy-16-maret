<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulking_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bulking_id');     // tetap uuid
            $table->uuid('user_detail_id'); // ubah jadi uuid
            $table->uuid('user_id')->nullable();
            $table->string('nip');
            $table->string('status')->default('processed'); 
            $table->text('message')->nullable();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulking_details');
    }
};