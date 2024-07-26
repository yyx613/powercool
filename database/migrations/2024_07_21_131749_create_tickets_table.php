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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('subject');
            $table->unsignedBigInteger('customer_id');
            $table->boolean('is_active');
            $table->longText('body');
            $table->unsignedBigInteger('last_touch_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('customer_id')->on('customers')->references('id');
            $table->foreign('last_touch_by')->on('users')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
