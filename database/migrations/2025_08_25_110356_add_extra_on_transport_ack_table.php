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
        Schema::table('transport_acknowledgements', function (Blueprint $table) {
            $table->string('delivery_order_id')->nullable()->after('customer_id');
            $table->integer('type')->nullable()->after('delivery_order_id');
            $table->string('company_name')->nullable()->after('type');
            $table->string('phone')->nullable()->after('company_name');
            $table->longText('address')->nullable()->after('phone');
        });
        Schema::create('transport_acknowledgement_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transport_acknowledgement_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('qty');
            $table->string('desc')->nullable();
            $table->longText('product_child_id')->nullable();
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
