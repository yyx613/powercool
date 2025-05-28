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
            $table->smallInteger('company_group_autocount')->nullable()->after('company_group');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->smallInteger('company_group_autocount')->nullable()->after('company_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('company_group_autocount');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('company_group_autocount');
        });
    }
};
