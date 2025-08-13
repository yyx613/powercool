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
            $table->boolean('sst')->default(false)->after('hi_ten_stock_code');
        });
        Schema::table('sale_products', function (Blueprint $table) {
            $table->boolean('with_sst')->default(false)->after('is_foc');
            $table->decimal('sst_amount')->nullable()->after('with_sst');
            $table->decimal('sst_value')->nullable()->after('sst_amount')->comment('SST (In %)');
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
