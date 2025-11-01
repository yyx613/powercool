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
        Schema::table('product_children', function (Blueprint $table) {
            $table->longText('reject_reason')->nullable()->after('stock_out_at');
        });
        Schema::table('factory_raw_materials', function (Blueprint $table) {
            $table->integer('status')->nullable()->after('to_warehouse_qty');
            $table->unsignedBigInteger('factory_id')->nullable()->after('status');
            $table->longText('reject_reason')->nullable()->after('factory_id');
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
