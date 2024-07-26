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
        Schema::table('task_milestone', function (Blueprint $table) {
            $table->longText('address')->nullable()->after('milestone_id');
            $table->timestamp('datetime')->nullable()->after('address');
            $table->decimal('amount_collected')->nullable()->after('datetime');
            $table->longText('remark')->nullable()->after('amount_collected');
            $table->timestamp('submitted_at')->nullable()->after('remark');
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
