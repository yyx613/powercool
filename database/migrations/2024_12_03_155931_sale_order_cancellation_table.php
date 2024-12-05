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
        Schema::create('sale_order_cancellation', function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id')->comment('SO ID');
            $table->unsignedBigInteger('on_hold_sale_id')->nullable()->comment('On Hold for cancelled SO');
            $table->unsignedBigInteger('saleperson_id');
            $table->unsignedBigInteger('product_id');
            $table->bigInteger('qty')->comment('Qty sales person required to sale');
            $table->bigInteger('extra')->nullable()->comment('Hold for extra qty deducted');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
