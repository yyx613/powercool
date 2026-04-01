<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_form_service_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_form_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('model_no')->nullable();
            $table->string('serial_no')->nullable();
            $table->unsignedInteger('sequence')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('service_form_id')->references('id')->on('service_forms')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });

        // Migrate existing single-product data into the new table
        DB::table('service_forms')
            ->where(function ($q) {
                $q->whereNotNull('product_id')
                    ->orWhereNotNull('model_no')
                    ->orWhereNotNull('serial_no');
            })
            ->orderBy('id')
            ->chunk(100, function ($forms) {
                foreach ($forms as $form) {
                    DB::table('service_form_service_items')->insert([
                        'service_form_id' => $form->id,
                        'product_id' => $form->product_id,
                        'model_no' => $form->model_no,
                        'serial_no' => $form->serial_no,
                        'sequence' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_form_service_items');
    }
};
