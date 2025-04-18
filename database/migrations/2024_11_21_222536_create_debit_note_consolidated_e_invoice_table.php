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
        Schema::create('debit_note_con_e_invoice', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('debit_note_id');
            $table->unsignedBigInteger('con_einvoice_id');
            $table->timestamps();

            $table->foreign('debit_note_id')->references('id')->on('debit_notes')->onDelete('cascade');
            $table->foreign('con_einvoice_id')->references('id')->on('consolidated_e_invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debit_note_consolidated_e_invoice');
    }
};
