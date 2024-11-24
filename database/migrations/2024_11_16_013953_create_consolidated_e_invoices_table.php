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
        Schema::create('consolidated_e_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('consolidated_e_invoice_invoice', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consolidated_e_invoice_id');
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('consolidated_e_invoice_id')->references('id')->on('consolidated_e_invoices')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consolidated_e_invoice_invoice');
        Schema::dropIfExists('consolidated_e_invoices');
    }
};
