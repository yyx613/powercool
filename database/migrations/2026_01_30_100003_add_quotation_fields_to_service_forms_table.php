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
        Schema::table('service_forms', function (Blueprint $table) {
            $table->decimal('subtotal', 12, 2)->nullable();
            $table->decimal('total_tax', 12, 2)->nullable();
            $table->decimal('grand_total', 12, 2)->nullable();
            $table->text('quotation_remark')->nullable();
            $table->string('validity')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable();

            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_forms', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn([
                'subtotal',
                'total_tax',
                'grand_total',
                'quotation_remark',
                'validity',
                'payment_method_id',
            ]);
        });
    }
};
