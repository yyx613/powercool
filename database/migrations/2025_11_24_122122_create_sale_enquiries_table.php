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
        Schema::create('sale_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->dateTime('enquiry_date');
            $table->tinyInteger('enquiry_source');
            $table->string('name');
            $table->string('phone_number', 50);
            $table->string('email')->nullable();
            $table->tinyInteger('preferred_contact_method')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('category')->nullable();
            $table->longText('description')->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users');
            $table->tinyInteger('priority')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_enquiries');
    }
};
