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
            $table->unsignedSmallInteger('payment_status')->nullable()->after('payment_amount');
            $table->longText('payment_amount')->change();
            $table->boolean('can_by_pass_conversion')->default(false)->after('payment_status');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('in_production')->default(false)->after('sku');
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
