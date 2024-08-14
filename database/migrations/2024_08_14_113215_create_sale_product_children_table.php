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
        Schema::create('sale_product_children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_product_id');
            $table->unsignedBigInteger('product_children_id');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('sale_product_id')->on('sale_products')->references('id');
            $table->foreign('product_children_id')->on('product_children')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_product_children');
    }
};
