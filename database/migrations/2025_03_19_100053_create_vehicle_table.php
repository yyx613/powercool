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
            $table->string('chasis');
            $table->string('buatan_nama_model');
            $table->string('keupayaan_enjin');
            $table->string('bahan_bakar');
            $table->string('status_asal');
            $table->string('kelas_kegunaan');
            $table->string('jenis_badan');
            $table->string('tarikh_pendaftaran');
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
