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
        // No-op: selling_price_id was already added in 2025_11_06_161526 migration
        // This migration originally changed selling_price to selling_price_id,
        // but the prior migration was updated to add selling_price_id directly.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
