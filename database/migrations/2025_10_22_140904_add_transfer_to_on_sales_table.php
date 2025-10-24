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
            $table->dropForeign('sales_sale_id_foreign');
            $table->unsignedBigInteger('transfer_from')->nullable()->after('cancellation_charge')->comment('Transferred from SO id (if not current branch), QUO id (if current branch)');
        });
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropForeign('delivery_orders_sale_id_foreign');
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
