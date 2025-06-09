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
        Schema::create('production_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('status');
            $table->longText('remark')->nullable();
            $table->unsignedBigInteger('requested_by');
            $table->timestamps();
        });
        Schema::create('production_request_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_request_id');
            $table->unsignedBigInteger('product_id')->comment('raw material');
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_requests');
        Schema::dropIfExists('production_request_materials');
    }
};
