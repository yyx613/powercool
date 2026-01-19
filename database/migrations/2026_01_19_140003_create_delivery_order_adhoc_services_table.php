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
        Schema::create('delivery_order_adhoc_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_adhoc_service_id')->constrained();
            $table->foreignId('adhoc_service_id')->constrained();
            $table->decimal('amount', 12, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_order_adhoc_services');
    }
};
