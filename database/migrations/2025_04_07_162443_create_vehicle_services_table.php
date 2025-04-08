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
        Schema::create('vehicle_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->timestamp('insurance_date')->nullable();
            $table->timestamp('insurance_remind_at')->nullable();
            $table->decimal('insurance_amount')->nullable();
            $table->timestamp('roadtax_date')->nullable();
            $table->timestamp('roadtax_remind_at')->nullable();
            $table->decimal('roadtax_amount')->nullable();
            $table->timestamp('inspection_date')->nullable();
            $table->timestamp('inspection_remind_at')->nullable();
            $table->timestamp('mileage_remind_at')->nullable();
            $table->decimal('petrol')->nullable();
            $table->decimal('toll')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_services');
    }
};
