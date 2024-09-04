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
        Schema::create('production_milestone_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_milestone_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('product_child_id')->nullable();
            $table->boolean('on_hold')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('production_milestone_id')->on('production_milestone')->references('id');
            $table->foreign('product_child_id')->on('product_children')->references('id');
            $table->foreign('product_id')->on('products')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_milestone_materials');
    }
};
