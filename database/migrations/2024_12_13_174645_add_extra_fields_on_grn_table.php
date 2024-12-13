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
        Schema::table('grn', function (Blueprint $table) {
            $table->string('our_po_no')->nullable()->after('sku');
            $table->timestamp('our_po_date')->nullable()->after('our_po_no');
            $table->unsignedBigInteger('term')->nullable()->after('our_po_date');
            $table->unsignedSmallInteger('branch_id')->nullable()->after('term');
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
