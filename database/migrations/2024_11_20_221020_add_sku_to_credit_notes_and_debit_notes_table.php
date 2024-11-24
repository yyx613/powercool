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
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('id');
        });

        Schema::table('debit_notes', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('id'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropColumn('sku');
        });

        Schema::table('debit_notes', function (Blueprint $table) {
            $table->dropColumn('sku');
        });
    }
};
