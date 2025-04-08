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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('department')->nullable()->after('tarikh_pendaftaran');
            $table->string('area_control')->nullable()->after('department');
            $table->unsignedBigInteger('vehicle_setting_id')->nullable()->after('area_control');
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
