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
        Schema::create('production_milestone_rejects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_milestone_id');
            $table->unsignedBigInteger('submitted_by')->comment('Who checked in the milestone');
            $table->timestamp('submitted_at')->comment('When the milestone is checked in');
            $table->unsignedBigInteger('rejected_by');
            $table->timestamps();
        });
        Schema::table('production_milestone_materials', function (Blueprint $table) {
            $table->unsignedBigInteger('production_milestone_reject_id')->nullable()->after('product_id');
        });
        Schema::table('production_milestone_materials', function (Blueprint $table) {
            $table->string('reject_reason')->nullable()->after('production_milestone_reject_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_milestone_rejects');
    }
};
