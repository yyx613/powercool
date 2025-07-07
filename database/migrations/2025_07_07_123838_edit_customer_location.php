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
        Schema::table('customer_locations', function (Blueprint $table) {
            $table->string('address2')->after('address')->nullable();
            $table->string('address3')->after('address2')->nullable();
            $table->string('address4')->after('address3')->nullable();
            $table->dropColumn('state');
            $table->renameColumn('address', 'address1');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->string('address')->after('type')->nullable()->comment('For TIN');
            $table->string('city')->after('address')->nullable()->comment('For TIN');
            $table->string('zipcode')->after('city')->nullable()->comment('For TIN');
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
