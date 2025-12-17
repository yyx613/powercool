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
        Schema::table('sale_payment_amounts', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_method')->nullable()->after('reference_number');
            $table->unsignedBigInteger('payment_term')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_payment_amounts', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_term']);
        });
    }
};
