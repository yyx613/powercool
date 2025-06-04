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
        Schema::create('raw_material_request_material_collected', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('raw_material_request_material_id');
            $table->integer('qty');
            $table->unsignedBigInteger('logged_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_material_request_material_collected');
    }
};
