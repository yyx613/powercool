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
        Schema::create('customize_products', function (Blueprint $table) {
            $table->id();

            // Product identification
            $table->string('sku');
            $table->unsignedBigInteger('production_id')->nullable();

            // Physical dimensions
            $table->decimal('weight', 10, 2)->nullable()->comment('In KG');
            $table->decimal('length', 10, 2)->nullable()->comment('In MM');
            $table->decimal('width', 10, 2)->nullable()->comment('In MM');
            $table->decimal('height', 10, 2)->nullable()->comment('In MM');

            // Technical specifications
            $table->string('capacity')->nullable();
            $table->string('refrigerant')->nullable();
            $table->string('power_input')->nullable();
            $table->string('power_consumption')->nullable();
            $table->string('voltage_frequency')->nullable();
            $table->string('standard_features')->nullable();

            // Timestamps
            $table->softDeletes();
            $table->timestamps();

            // Foreign key
            $table->foreign('production_id')
                ->references('id')
                ->on('productions')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customize_products');
    }
};
