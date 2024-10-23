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
        Schema::create('platform_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('platform'); 
            $table->string('access_token');
            $table->string('refresh_token'); 
            $table->timestamp('access_token_expires_at');
            $table->timestamp('refresh_token_expires_at'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_tokens');
    }
};
