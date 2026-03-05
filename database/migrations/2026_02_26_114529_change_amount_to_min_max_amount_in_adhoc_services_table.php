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
        Schema::table('adhoc_services', function (Blueprint $table) {
            $table->renameColumn('amount', 'min_amount');
        });

        Schema::table('adhoc_services', function (Blueprint $table) {
            $table->decimal('max_amount', 12, 2)->nullable()->after('min_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adhoc_services', function (Blueprint $table) {
            $table->dropColumn('max_amount');
        });

        Schema::table('adhoc_services', function (Blueprint $table) {
            $table->renameColumn('min_amount', 'amount');
        });
    }
};
