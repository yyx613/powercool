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
        Schema::create('service_form_product_warranty_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_form_product_id');
            $table->unsignedBigInteger('warranty_period_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_form_product_id', 'sf_prod_wp_sf_prod_id_fk')->references('id')->on('service_form_products')->onDelete('cascade');
            $table->foreign('warranty_period_id', 'sf_prod_wp_wp_id_fk')->references('id')->on('warranty_periods')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_form_product_warranty_periods');
    }
};
