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
        Schema::create('agent_debtors', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->string('company_name');
            $table->string('phone');
            $table->longText('address');
            $table->unsignedBigInteger('dealer_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_debtors');
    }
};
