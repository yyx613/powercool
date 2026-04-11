<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_forms', function (Blueprint $table) {
            $table->string('sr_sku')->nullable()->unique()->after('grand_total');
            $table->string('srq_sku')->nullable()->unique()->after('sr_sku');
            $table->string('srcs_sku')->nullable()->unique()->after('srq_sku');
            $table->string('sri_sku')->nullable()->unique()->after('srcs_sku');

            $table->dropColumn([
                'generated_service_form',
                'generated_quotation',
                'generated_cash_sale',
                'generated_invoice',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('service_forms', function (Blueprint $table) {
            $table->dropColumn(['sr_sku', 'srq_sku', 'srcs_sku', 'sri_sku']);

            $table->boolean('generated_service_form')->default(false)->after('grand_total');
            $table->boolean('generated_quotation')->default(false)->after('generated_service_form');
            $table->boolean('generated_cash_sale')->default(false)->after('generated_quotation');
            $table->boolean('generated_invoice')->default(false)->after('generated_cash_sale');
        });
    }
};
