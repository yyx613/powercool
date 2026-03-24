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
        Schema::table('sale_products', function (Blueprint $table) {
            $table->string('discount_type')->default('fixed')->after('discount');
        });

        Schema::table('service_form_products', function (Blueprint $table) {
            $table->string('discount_type')->default('fixed')->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_products', function (Blueprint $table) {
            $table->dropColumn('discount_type');
        });

        Schema::table('service_form_products', function (Blueprint $table) {
            $table->dropColumn('discount_type');
        });
    }
};
