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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_category_id');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedInteger('type')->comment('1 - Product, 2 - Raw Material');
            $table->string('sku');
            $table->string('model_name');
            $table->string('model_desc');
            $table->unsignedInteger('qty');
            $table->decimal('price');
            $table->decimal('weight')->nullable()->comment('In KG');
            $table->decimal('length')->nullable()->comment('In cm');
            $table->decimal('width')->nullable()->comment('In cm');
            $table->decimal('height')->nullable()->comment('In cm');
            $table->boolean('is_active');
            $table->boolean('is_sparepart')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('inventory_category_id')->on('inventory_categories')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
