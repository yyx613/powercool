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
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign('sales_payment_term_foreign');
            $table->dropColumn('payment_term');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_term')->nullable()->after('remark');

            $table->foreign('payment_term')->on('credit_terms')->references('id');
        });
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropColumn('payment_terms');
        });
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_terms')->nullable()->after('sale_id');

            $table->foreign('payment_terms')->on('credit_terms')->references('id');
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
