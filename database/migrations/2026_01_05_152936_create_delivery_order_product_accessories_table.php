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
        Schema::create('delivery_order_product_accessories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_order_product_id');
            $table->unsignedBigInteger('sale_product_accessory_id');
            $table->unsignedBigInteger('accessory_id');
            $table->integer('qty');
            $table->boolean('is_foc')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('delivery_order_product_id', 'dopa_dop_id_foreign')
                ->references('id')
                ->on('delivery_order_products')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_order_product_accessories');
    }
};
