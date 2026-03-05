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
        Schema::table('sale_adhoc_services', function (Blueprint $table) {
            $table->decimal('sst_value', 5, 2)->nullable()->after('is_sst')->comment('SST (In %)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_adhoc_services', function (Blueprint $table) {
            $table->dropColumn('sst_value');
        });
    }
};
