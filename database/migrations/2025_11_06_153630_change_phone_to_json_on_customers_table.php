<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, convert existing mobile_number data to JSON array format
        DB::statement('UPDATE customers SET mobile_number = JSON_ARRAY(mobile_number) WHERE mobile_number IS NOT NULL AND mobile_number != ""');

        // Update empty or null mobile_number values to empty JSON array
        DB::statement('UPDATE customers SET mobile_number = JSON_ARRAY() WHERE mobile_number IS NULL OR mobile_number = ""');

        // Change the column type to JSON
        Schema::table('customers', function (Blueprint $table) {
            $table->json('mobile_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert JSON array back to first mobile number as string
        DB::statement('UPDATE customers SET mobile_number = JSON_UNQUOTE(JSON_EXTRACT(mobile_number, "$[0]")) WHERE JSON_TYPE(mobile_number) = "ARRAY"');

        // Change the column type back to string
        Schema::table('customers', function (Blueprint $table) {
            $table->string('mobile_number')->nullable()->change();
        });
    }
};
