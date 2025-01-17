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
            $table->dropColumn('delivery_date');
            $table->dropColumn('delivery_time');
            $table->dropForeign('sales_driver_id_foreign');
            $table->dropColumn('driver_id');
            $table->dropColumn('delivery_instruction');
            $table->dropForeign('sales_delivery_address_id_foreign');
            $table->dropColumn('delivery_address_id');
            $table->dropColumn('delivery_is_active');
            $table->unsignedBigInteger('billing_address_id')->nullable()->after('customer_id');
            $table->longText('billing_address')->nullable()->after('billing_address_id');
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
