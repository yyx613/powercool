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
            $table->string('sku')->nullable()->after('id');
            $table->date('date')->nullable()->after('sku');
            $table->bigInteger('dealer_id')->nullable()->after('date');
            $table->unsignedBigInteger('customer_id')->nullable()->after('dealer_id');
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
