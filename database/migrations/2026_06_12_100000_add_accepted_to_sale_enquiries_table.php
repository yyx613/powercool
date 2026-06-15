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
            $table->timestamp('accepted_at')->nullable()->after('status');
            $table->foreignId('accepted_by')->nullable()->after('accepted_at')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_enquiries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('accepted_by');
            $table->dropColumn('accepted_at');
        });
    }
};
