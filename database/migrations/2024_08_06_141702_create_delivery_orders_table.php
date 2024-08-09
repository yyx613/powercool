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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('sale_id')->comment('Salesperson id');
            $table->string('payment_terms');
            $table->string('filename');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('invoice_id')->on('invoices')->references('id');
            $table->foreign('customer_id')->on('customers')->references('id');
            $table->foreign('sale_id')->on('users')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
