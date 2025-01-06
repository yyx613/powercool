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
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedSmallInteger('company_group')->nullable()->after('sku');
        });
        Schema::table('suppliers', function (Blueprint $table) {
            $table->unsignedSmallInteger('company_group')->nullable()->after('sku');
        });
        Schema::table('grn', function (Blueprint $table) {
            $table->unsignedSmallInteger('company_group')->nullable()->after('sku');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedSmallInteger('company_group')->nullable()->after('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
