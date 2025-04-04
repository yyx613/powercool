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
        Schema::table('production_milestone', function (Blueprint $table) {
            $table->dropColumn('material_use_product_id');
        });

        Schema::create('production_milestone_materials_preview', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_milestone_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedInteger('qty');
            $table->timestamps();
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
