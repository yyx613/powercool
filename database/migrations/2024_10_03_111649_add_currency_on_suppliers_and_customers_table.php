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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('phone');
            $table->dropColumn('under_warranty');

            $table->foreign('currency_id')->on('currencies')->references('id');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('phone');
            $table->dropColumn('under_warranty');

            $table->foreign('currency_id')->on('currencies')->references('id');
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
