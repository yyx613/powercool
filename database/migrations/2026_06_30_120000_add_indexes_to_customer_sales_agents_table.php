<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The customer_sales_agents pivot only had a PRIMARY key on `id`, so sorting the
 * Debtor listing by "Sales Agents" (a correlated GROUP_CONCAT subquery keyed on
 * customer_id) full-scanned the pivot once per customer. Index the FK columns so
 * the lookups — and the salesAgents relation generally — are fast.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_sales_agents', function (Blueprint $table) {
            $table->index('customer_id', 'csa_customer_id_index');
            $table->index('sales_agent_id', 'csa_sales_agent_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('customer_sales_agents', function (Blueprint $table) {
            $table->dropIndex('csa_customer_id_index');
            $table->dropIndex('csa_sales_agent_id_index');
        });
    }
};
