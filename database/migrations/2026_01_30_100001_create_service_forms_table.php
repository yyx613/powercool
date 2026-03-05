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
        Schema::create('service_forms', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->date('date')->nullable();

            // Customer Information
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('customer_location_id')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_no')->nullable();

            // Product Information
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('model_no')->nullable();
            $table->string('serial_no')->nullable();

            // Invoice Information
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->date('invoice_date')->nullable();
            $table->tinyInteger('warranty_status')->nullable(); // 1=Under Warranty, 2=Out of Warranty

            // Dealer Information
            $table->unsignedBigInteger('dealer_id')->nullable();
            $table->string('dealer_name')->nullable();
            $table->string('dealer_contact_no')->nullable();

            // Service Details
            $table->text('nature_of_problem')->nullable();
            $table->date('date_to_attend')->nullable();
            $table->unsignedBigInteger('technician_id')->nullable();

            // Report Checklist
            $table->json('report_checklist')->nullable();

            // Foreign Keys
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('customer_location_id')->references('id')->on('customer_locations')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('set null');
            $table->foreign('technician_id')->references('id')->on('users')->onDelete('set null');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_forms');
    }
};
