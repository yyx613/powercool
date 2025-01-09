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
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_address_id')->nullable()->after('payment_terms');
            $table->longText('delivery_address')->nullable()->after('delivery_address_id');
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
