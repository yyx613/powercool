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
            $table->string('tin_number')->nullable()->after('remark');
            $table->unsignedBigInteger('sale_agent')->nullable()->after('tin_number');
            $table->unsignedBigInteger('area_id')->nullable()->after('sale_agent');
            $table->unsignedBigInteger('debtor_type_id')->nullable()->after('area_id');

            $table->foreign('sale_agent')->on('users')->references('id');
            $table->foreign('area_id')->on('areas')->references('id');
            $table->foreign('debtor_type_id')->on('debtor_types')->references('id');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->string('tin_number')->nullable()->after('remark');
            $table->unsignedBigInteger('sale_agent')->nullable()->after('tin_number');
            $table->unsignedBigInteger('area_id')->nullable()->after('sale_agent');
            $table->unsignedBigInteger('debtor_type_id')->nullable()->after('area_id');

            $table->foreign('sale_agent')->on('users')->references('id');
            $table->foreign('area_id')->on('areas')->references('id');
            $table->foreign('debtor_type_id')->on('debtor_types')->references('id');
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
