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
        Schema::create('grn', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('qty');
            $table->string('uom');
            $table->decimal('unit_price');
            $table->decimal('total_price');
            $table->timestamps();

            $table->foreign('supplier_id')->on('suppliers')->references('id');
            $table->foreign('product_id')->on('products')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grn');
    }
};
