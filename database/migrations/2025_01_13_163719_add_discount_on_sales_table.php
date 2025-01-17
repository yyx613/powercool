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
        Schema::table('sale_products', function (Blueprint $table) {
            $table->decimal('discount')->nullable()->after('promotion_id');
            $table->longText('remark')->nullable()->after('discount');
            $table->decimal('override_selling_price')->nullable()->after('selling_price_id');
        });
        Schema::table('credit_terms', function (Blueprint $table) {
            $table->boolean('by_pass_conversion')->default(false)->after('name');
        });
        Schema::table('uom', function (Blueprint $table) {
            $table->integer('company_group')->default(null)->after('name');
        });
        Schema::table('inventory_categories', function (Blueprint $table) {
            $table->integer('company_group')->default(null)->after('name');
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
