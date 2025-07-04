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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->unsignedInteger('type')->comment('1 - QUO, 2 - SO');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('convert_to')->nullable()->comment('QUO convert to which SO, SO convert to which DO');
            $table->unsignedBigInteger('sale_id')->comment('Sales Agents ID');
            $table->date('open_until')->nullable();
            $table->string('quo_from')->nullable();
            $table->string('quo_cc')->nullable();
            $table->longText('reference');
            $table->unsignedInteger('status');
            $table->unsignedInteger('report_type');
            $table->longText('remark')->nullable();
            $table->string('payment_term')->nullable();
            $table->string('payment_method')->nullable();
            $table->date('payment_due_date')->nullable();
            $table->decimal('payment_amount')->nullable();
            $table->string('payment_remark')->nullable();
            $table->date('delivery_date')->nullable();
            $table->time('delivery_time')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('delivery_instruction')->nullable();
            $table->unsignedBigInteger('delivery_address_id')->nullable();
            $table->boolean('delivery_is_active')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('customer_id')->on('customers')->references('id');
            $table->foreign('sale_id')->on('users')->references('id');
            $table->foreign('driver_id')->on('users')->references('id');
            $table->foreign('delivery_address_id')->on('customer_locations')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
