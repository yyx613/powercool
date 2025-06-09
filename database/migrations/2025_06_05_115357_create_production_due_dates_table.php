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
        Schema::create('production_due_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_id');
            $table->date('old_date');
            $table->date('new_date');
            $table->unsignedBigInteger('done_by');
            $table->longText('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_due_dates');
    }
};
