<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('order_id')->nullable()->after('delivery_is_active');
            $table->unsignedBigInteger('platform_id')->nullable()->after('sku');

            $table->foreign('platform_id')->on('platforms')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['platform_id']); 
            $table->dropColumn(['order_id', 'platform_id']);
        });
    }
};
