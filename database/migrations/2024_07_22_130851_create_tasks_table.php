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
            $table->string('sku');
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->unsignedInteger('task_type')->nullable()->comment('technician service type - service/installer');
            $table->unsignedInteger('type')->comment('type for either driver/sale/technician');
            $table->unsignedBigInteger('customer_id');
            $table->string('name');
            $table->string('desc');
            $table->date('start_date');
            $table->date('due_date');
            $table->string('remark')->nullable();
            $table->unsignedInteger('status');
            $table->decimal('amount_to_collect')->default(0);
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
