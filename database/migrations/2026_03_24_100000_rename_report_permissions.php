<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $renames = [
            'report.production.view' => 'report.production',
            'report.sales.view' => 'report.sales',
            'report.stock.view' => 'report.stock',
            'report.earning.view' => 'report.earning',
            'report.service.view' => 'report.service',
            'report.technician_stock.view' => 'report.technician_stock',
        ];

        foreach ($renames as $old => $new) {
            DB::table('permissions')
                ->where('name', $old)
                ->where('guard_name', 'web')
                ->update(['name' => $new]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $renames = [
            'report.production' => 'report.production.view',
            'report.sales' => 'report.sales.view',
            'report.stock' => 'report.stock.view',
            'report.earning' => 'report.earning.view',
            'report.service' => 'report.service.view',
            'report.technician_stock' => 'report.technician_stock.view',
        ];

        foreach ($renames as $old => $new) {
            DB::table('permissions')
                ->where('name', $old)
                ->where('guard_name', 'web')
                ->update(['name' => $new]);
        }
    }
};
