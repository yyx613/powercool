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
            $table->timestamp('rejected_at')->nullable()->after('accepted_by');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_enquiries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn('rejected_at');
        });
    }
};
