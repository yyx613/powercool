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
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('sale_id');
            $table->string('name');
            $table->string('desc');
            $table->date('start_date');
            $table->date('due_date');
            $table->string('remark')->nullable();
            $table->unsignedInteger('status');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('product_id')->on('products')->references('id');
            $table->foreign('sale_id')->on('sales')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
