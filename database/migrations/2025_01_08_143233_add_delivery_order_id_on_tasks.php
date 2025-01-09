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
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_order_id')->nullable()->after('sale_order_id');
            $table->unsignedBigInteger('delivery_address_id')->nullable()->after('delivery_order_id');
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
