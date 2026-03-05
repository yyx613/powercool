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
        Schema::create('service_form_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_form_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('custom_desc')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->boolean('is_foc')->default(false);
            $table->boolean('with_sst')->default(false);
            $table->decimal('sst_amount', 12, 2)->nullable();
            $table->decimal('sst_value', 5, 2)->nullable();
            $table->decimal('discount', 12, 2)->nullable();
            $table->string('uom')->nullable();
            $table->text('remark')->nullable();
            $table->unsignedInteger('sequence')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('service_form_id')->references('id')->on('service_forms')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_form_products');
    }
};
