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
        Schema::create('inventory_service_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('object');
            $table->timestamp('next_service_date');
            $table->unsignedSmallInteger('reminding_days')->nullable()->comment('before next service date');
            $table->timestamp('reminded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_service_histories');
    }
};
