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
            $table->unsignedBigInteger('transfer_by')->nullable()->after('transferred_from');
            $table->timestamp('transfer_at')->nullable()->after('transfer_by');
            $table->unsignedBigInteger('stock_out_by')->nullable()->after('transfer_at');
            $table->timestamp('stock_out_at')->nullable()->after('stock_out_by');

            $table->foreign('transfer_by')->on('users')->references('id');
            $table->foreign('stock_out_by')->on('users')->references('id');
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
