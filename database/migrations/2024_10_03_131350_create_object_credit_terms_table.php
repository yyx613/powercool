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
        Schema::create('object_credit_terms', function (Blueprint $table) {
            $table->id();
            $table->morphs('object');
            $table->unsignedBigInteger('credit_term_id');
            $table->timestamps();

            $table->foreign('credit_term_id')->on('credit_terms')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_credit_terms');
    }
};
