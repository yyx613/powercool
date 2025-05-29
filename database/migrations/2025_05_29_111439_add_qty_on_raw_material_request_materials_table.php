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
        Schema::table('raw_material_request_materials', function (Blueprint $table) {
            $table->integer('qty')->nullable()->after('status');
            $table->integer('qty_collected')->nullable()->after('qty')->comment('For raw material');
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
