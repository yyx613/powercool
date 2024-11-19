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
            $table->string("stock_out_to_type")->after('stock_out_by');
            $table->unsignedBigInteger("stock_out_to_id")->after('stock_out_to_type');
            $table->index(["stock_out_to_id", "stock_out_to_type"]);
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
