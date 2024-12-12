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
            $table->dropColumn('barcode');

            $table->string('capacity')->nullable()->after('height');
            $table->string('refrigerant')->nullable()->after('capacity');
            $table->string('power_input')->nullable()->after('refrigerant');
            $table->string('voltage_frequency')->nullable()->after('power_input');
            $table->string('standard_features')->nullable()->after('voltage_frequency');
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
