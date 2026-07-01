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
        Schema::table('return_products', function (Blueprint $table) {
            // Why the item was returned (carried over from the approval request)
            // and which delivery order it was delivered under, so the "view
            // returned products" screen can show the DO and reason per item.
            $table->text('reason')->nullable()->after('qty');
            $table->unsignedBigInteger('delivery_order_id')->nullable()->after('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_products', function (Blueprint $table) {
            $table->dropColumn(['reason', 'delivery_order_id']);
        });
    }
};
