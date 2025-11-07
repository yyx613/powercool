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
        Schema::table('sale_product_accessories', function (Blueprint $table) {
            $table->integer('qty')->nullable()->after('accessory_id');
            $table->unsignedInteger('selling_price_id')->nullable()->after('qty');
            $table->decimal('override_selling_price', 10, 2)->nullable()->after('selling_price_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_product_accessories', function (Blueprint $table) {
            $table->dropColumn(['qty', 'selling_price_id', 'override_selling_price']);
        });
    }
};
