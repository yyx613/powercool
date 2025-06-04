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
        Schema::create('raw_material_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_id');
            $table->unsignedBigInteger('material_use_id');
            $table->integer('status');
            $table->unsignedBigInteger('requested_by');
            $table->timestamps();
        });
        Schema::create('raw_material_request_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('raw_material_request_id');
            $table->unsignedBigInteger('product_id')->comment('raw material');
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_material_requests');
        Schema::dropIfExists('raw_material_request_materials');
    }
};
