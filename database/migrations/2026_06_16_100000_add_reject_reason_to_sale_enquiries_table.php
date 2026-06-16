<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_enquiries', function (Blueprint $table) {
            // Reason the assigned salesperson gives when rejecting the enquiry.
            $table->text('reject_reason')->nullable()->after('rejected_by');
        });
    }

    public function down(): void
    {
        Schema::table('sale_enquiries', function (Blueprint $table) {
            $table->dropColumn('reject_reason');
        });
    }
};
