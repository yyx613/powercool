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
        Schema::create('sale_payment_amounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->decimal('amount');
            $table->date('date');
            $table->string('reference_number')->nullable();
            $table->unsignedBigInteger('by_id');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('payment_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payment_amounts');
    }
};
