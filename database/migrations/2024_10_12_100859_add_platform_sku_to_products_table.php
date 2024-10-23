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
        Schema::table('products', function (Blueprint $table) {
            $table->string('lazada_sku')->nullable()->after('is_sparepart');
            $table->string('shopee_sku')->nullable()->after('lazada_sku');
            $table->string('tiktok_sku')->nullable()->after('shopee_sku');
            $table->string('woo_commerce_sku')->nullable()->after('tiktok_sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['lazada_sku', 'shopee_sku','tiktok_sku','woo_commerce_sku']);
        });
    }
};
