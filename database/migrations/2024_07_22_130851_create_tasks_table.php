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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->unsignedInteger('task_type')->nullable();
            $table->unsignedInteger('type');
            $table->unsignedBigInteger('customer_id');
            $table->string('name');
            $table->string('desc');
            $table->date('start_date');
            $table->date('due_date');
            $table->string('remark')->nullable();
            $table->unsignedInteger('priority');
            $table->unsignedInteger('status');
            $table->boolean('collect_payment');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('customer_id')->on('customers')->references('id');
            $table->foreign('ticket_id')->on('tickets')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
