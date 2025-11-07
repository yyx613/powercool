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
            $table->dropColumn('selling_price');
            $table->unsignedInteger('selling_price_id')->nullable()->after('qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_product_accessories', function (Blueprint $table) {
            $table->dropColumn('selling_price_id');
            $table->decimal('selling_price', 10, 2)->nullable()->after('qty');
        });
    }
};
