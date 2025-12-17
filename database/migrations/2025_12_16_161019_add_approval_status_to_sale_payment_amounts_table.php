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
        Schema::table('sale_payment_amounts', function (Blueprint $table) {
            $table->tinyInteger('approval_status')->default(1)->after('by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_payment_amounts', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};
