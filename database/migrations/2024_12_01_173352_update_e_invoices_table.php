<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('e_invoices', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');

            $table->unsignedBigInteger('einvoiceable_id');
            $table->string('einvoiceable_type');

            $table->index(['einvoiceable_id', 'einvoiceable_type']);
        });
    }

    public function down()
    {
        Schema::table('e_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->unique();
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');

            $table->dropIndex(['einvoiceable_id', 'einvoiceable_type']);
            $table->dropColumn('einvoiceable_id');
            $table->dropColumn('einvoiceable_type');
        });
    }
};
