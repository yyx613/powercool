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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('custom_customer')->nullable()->after('customer_id')->comment('For cash sale only');
            $table->string('custom_mobile')->nullable()->after('custom_customer')->comment('For cash sale only');
            $table->integer('company_group')->nullable()->after('custom_mobile')->comment('For cash sale only');
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
