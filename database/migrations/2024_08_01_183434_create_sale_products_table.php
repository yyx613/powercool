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
        Schema::create('sale_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->string('name');
            $table->string('desc')->nullable();
            $table->unsignedInteger('qty');
            $table->decimal('unit_price');
            $table->string('warranty_period');
            $table->string('serial_no')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('sale_id')->on('sales')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_products');
    }
};
