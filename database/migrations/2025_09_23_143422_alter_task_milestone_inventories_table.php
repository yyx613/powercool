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
        Schema::table('task_milestone_inventories', function (Blueprint $table) {
            $table->bigInteger('task_milestone_id')->nullable()->change();
            $table->bigInteger('pc_id')->nullable()->after('task_milestone_id');
            $table->date('service_date')->nullable()->after('qty');
            $table->unsignedBigInteger('service_by')->nullable()->after('service_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
