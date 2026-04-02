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
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('customer_signature')->nullable()->after('amount_to_collect');
            $table->timestamp('photos_approved_at')->nullable()->after('customer_signature');
            $table->timestamp('signed_off_at')->nullable()->after('photos_approved_at');
            $table->string('signed_off_by')->nullable()->after('signed_off_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['customer_signature', 'photos_approved_at', 'signed_off_at', 'signed_off_by']);
        });
    }
};
