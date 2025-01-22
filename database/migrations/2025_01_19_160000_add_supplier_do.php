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
            $table->string('supplier_do_no')->nullable()->after('our_po_date'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grn', function (Blueprint $table) {
            $table->dropColumn('supplier_do_no');
        });
    }
};
