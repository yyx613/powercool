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
            $table->string('sku')->nullable()->after('id');
            $table->string('mobile_number')->nullable()->after('phone');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('id');
            $table->string('mobile_number')->nullable()->after('phone');
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
