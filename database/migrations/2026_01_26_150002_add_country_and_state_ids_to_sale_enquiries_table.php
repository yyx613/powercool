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
        Schema::table('sale_enquiries', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('state')->constrained('countries')->nullOnDelete();
            $table->foreignId('state_id')->nullable()->after('country_id')->constrained('states')->nullOnDelete();
            $table->dropColumn(['country', 'state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_enquiries', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['country_id']);
            $table->dropColumn(['state_id', 'country_id']);
            $table->string('country')->nullable()->after('preferred_contact_method');
            $table->string('state')->nullable()->after('country');
        });
    }
};
