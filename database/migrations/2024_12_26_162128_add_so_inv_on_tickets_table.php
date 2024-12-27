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
        Schema::table('tickets', function (Blueprint $table) {
            $table->longText('so_inv')->nullable()->after('body');
            $table->longText('so_inv_type')->nullable()->after('so_inv');
            $table->longText('product_id')->nullable()->after('so_inv_type');
            $table->longText('product_child_id')->nullable()->after('product_id');
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
