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
        Schema::table('inventory_service_reminders', function (Blueprint $table) {
            $table->string("attached_type")->nullable()->after('object_id');
            $table->unsignedBigInteger("attached_id")->nullable()->after('attached_type');
            $table->index(["attached_id", "attached_type"]);
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
