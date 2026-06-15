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
            // Reason captured when the salesperson marks the enquiry as "No Deal".
            // Persisted once management approves the No-Deal request.
            $table->text('no_deal_reason')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_enquiries', function (Blueprint $table) {
            $table->dropColumn('no_deal_reason');
        });
    }
};
