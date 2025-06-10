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
        Schema::create('sale_product_warranty_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_product_id');
            $table->unsignedBigInteger('warranty_period_id');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('sale_products', function (Blueprint $table) {
            $table->dropForeign(['warranty_period_id']);
            $table->dropColumn('warranty_period_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_product_warranty_periods');
    }
};
