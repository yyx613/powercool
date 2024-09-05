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
        Schema::create('product_children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('sku');
            $table->string('location');
            $table->unsignedInteger('status')->nullable();
            $table->unsignedBigInteger('transferred_from')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('product_id')->on('products')->references('id');
            $table->foreign('transferred_from')->on('product_children')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_children');
    }
};
