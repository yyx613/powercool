<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('billing_sale_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billing_id');
            $table->unsignedBigInteger('sale_product_id');
            $table->decimal('custom_unit_price', 10, 2); // Custom price for each product
            $table->timestamps();

            $table->foreign('billing_id')->references('id')->on('billings')->onDelete('cascade');
            $table->foreign('sale_product_id')->references('id')->on('sale_products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('billing_sale_product');
    }
};
