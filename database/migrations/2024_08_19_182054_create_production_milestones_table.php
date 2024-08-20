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
        Schema::create('production_milestone', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_id');
            $table->unsignedBigInteger('milestone_id');
            $table->boolean('required_serial_no');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('production_id')->on('productions')->references('id');
            $table->foreign('milestone_id')->on('milestones')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_milestones');
    }
};
