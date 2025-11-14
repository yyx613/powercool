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
            $table->boolean('is_foc')->default(false)->after('override_selling_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_product_accessories', function (Blueprint $table) {
            $table->dropColumn('is_foc');
        });
    }
};
