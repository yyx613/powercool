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
        Schema::table('currencies', function (Blueprint $table) {
            $table->string('country')->nullable()->after('name');
            $table->string('currency_name')->nullable()->after('country');
            $table->string('code')->nullable()->after('currency_name');
            $table->string('symbol')->nullable()->after('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn(['country', 'currency_name', 'code', 'symbol']);
        });
    }
};
