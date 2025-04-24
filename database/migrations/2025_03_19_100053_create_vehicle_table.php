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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number');
            $table->string('chasis')->nullable();
            $table->string('buatan_nama_model')->nullable();
            $table->string('keupayaan_enjin')->nullable();
            $table->string('bahan_bakar')->nullable();
            $table->string('status_asal')->nullable();
            $table->string('kelas_kegunaan')->nullable();
            $table->string('jenis_badan')->nullable();
            $table->string('tarikh_pendaftaran')->nullable();
            $table->string('department')->nullable();
            $table->string('area_control')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle');
    }
};
