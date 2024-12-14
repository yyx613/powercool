<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCanSubmitEinvoiceToPlatformsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('platforms', function (Blueprint $table) {
            $table->boolean('can_submit_einvoice')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platforms', function (Blueprint $table) {
            $table->dropColumn('can_submit_einvoice');
        });
    }
}
