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
        Schema::create('material_use_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_use_id');
            $table->unsignedBigInteger('product_id')->comment('raw material');
            $table->unsignedInteger('qty');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('material_use_id')->on('material_uses')->references('id');
            $table->foreign('product_id')->on('products')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_uses_products');
    }
};
