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
        Schema::create('factory_raw_material_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('factory_raw_material_id');
            $table->unsignedBigInteger('production_id')->nullable();
            $table->integer('qty');
            $table->unsignedBigInteger('uom')->nullable();
            $table->longText('remark')->nullable();
            $table->unsignedBigInteger('done_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factory_raw_material_records');
    }
};
