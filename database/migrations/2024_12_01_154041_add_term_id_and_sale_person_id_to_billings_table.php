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
        Schema::table('billings', function (Blueprint $table) {
            $table->unsignedBigInteger('term_id')->nullable()->after('inv_filename');
            $table->unsignedBigInteger('sale_person_id')->nullable()->after('term_id');
            $table->string('our_do_no')->nullable()->after('sale_person_id');
            $table->foreign('term_id')->references('id')->on('credit_terms')->onDelete('set null');
            $table->foreign('sale_person_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
            $table->dropForeign(['sale_person_id']);
            $table->dropColumn(['term_id', 'sale_person_id']);
        });
    }

};
