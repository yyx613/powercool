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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->boolean('sync')->nullable()->after('location'); 
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('sync')->nullable()->after('remark'); 
        });
        Schema::table('grn', function (Blueprint $table) {
            $table->boolean('sync')->nullable()->after('total_price'); 
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('sync')->nullable()->after('status'); 
        });
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('sync')->nullable()->after('sku'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('sync');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('sync');
        });
        Schema::table('grn', function (Blueprint $table) {
            $table->dropColumn('sync');
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('sync');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sync');
        });
    }
};
