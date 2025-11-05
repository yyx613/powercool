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
        Schema::table('vehicle_service_items', function (Blueprint $table) {
            $table->date('warranty_expiry_date')->nullable()->after('amount');
            $table->string('warranty_term')->nullable()->after('warranty_expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_service_items', function (Blueprint $table) {
            $table->dropColumn(['warranty_expiry_date', 'warranty_term']);
        });
    }
};
