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
        // Schema::table('platform_tokens', function (Blueprint $table) {
        //     $table->dropColumn('platform');

        //     $table->unsignedBigInteger('platform_id')->after('id');
        //     $table->foreign('platform_id')->references('id')->on('platforms')->onDelete('cascade');
        // });
    }

    public function down(): void
    {
        Schema::table('platform_tokens', function (Blueprint $table) {
            $table->dropForeign(['platform_id']);
            $table->dropColumn('platform_id');

            $table->string('platform')->after('id');
        });
    }
};
