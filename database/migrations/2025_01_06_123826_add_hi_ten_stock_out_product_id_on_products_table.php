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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('hi_ten_stock_code')->nullable()->after('item_type');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->string('category')->nullable()->after('company_group');
            $table->longText('business_act_desc')->nullable()->after('msic_id');
            $table->string('tourism_tax_reg_no')->nullable()->after('business_act_desc');
            $table->string('prev_gst_reg_no')->nullable()->after('business_act_desc');
            $table->string('registered_name')->nullable()->after('prev_gst_reg_no');
            $table->string('trade_name')->nullable()->after('registered_name');
            $table->string('identity_type')->nullable()->after('trade_name');
            $table->string('identity_no')->nullable()->after('identity_type');
        });
        Schema::table('suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('msic_id')->nullable()->after('id');
            $table->string('category')->nullable()->after('company_group');
            $table->longText('business_act_desc')->nullable()->after('msic_id');
            $table->string('tourism_tax_reg_no')->nullable()->after('business_act_desc');
            $table->string('prev_gst_reg_no')->nullable()->after('business_act_desc');
            $table->string('registered_name')->nullable()->after('prev_gst_reg_no');
            $table->string('trade_name')->nullable()->after('registered_name');
            $table->string('identity_type')->nullable()->after('trade_name');
            $table->string('identity_no')->nullable()->after('identity_type');
            $table->string('sst_number')->nullable()->after('id');
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
