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
            $table->tinyInteger('quality')->nullable()->after('status');
            $table->unsignedBigInteger('promotion_id')->nullable()->after('quality');

            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_enquiries', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
            $table->dropColumn(['quality', 'promotion_id']);
        });
    }
};
