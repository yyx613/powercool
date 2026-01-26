<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetModulesSeeder extends Seeder
{
    /**
     * Reset the following modules by clearing database records:
     * - Approvals
     * - Sale Enquiries
     * - Quotations (Sales type=1)
     * - Sale Orders (Sales type=2)
     * - Delivery Orders
     * - Invoices
     * - Invoice Returns (CreditNotes, DebitNotes, ReturnProducts)
     * - Tasks
     * - Production Requests
     *
     * WARNING: This is a destructive operation that permanently deletes all records
     * including soft-deleted ones. Backup your database before running.
     *
     * Run with: php artisan db:seed --class=ResetModulesSeeder
     */
    public function run(): void
    {
        $this->command->warn('Starting module reset...');
        $this->command->warn('This will PERMANENTLY DELETE all records in the affected tables.');

        DB::beginTransaction();

        try {
            // Disable foreign key checks for safer deletion
            Schema::disableForeignKeyConstraints();

            // Phase 1: Clear Pivot/Junction Tables
            $this->command->info('Phase 1: Clearing pivot/junction tables...');
            $this->clearPivotTables();

            // Phase 2: Clear Child/Detail Tables
            $this->command->info('Phase 2: Clearing child/detail tables...');
            $this->clearChildTables();

            // Phase 3: Clear E-Invoice Related Tables
            $this->command->info('Phase 3: Clearing e-invoice related tables...');
            $this->clearEInvoiceTables();

            // Phase 4: Clear Approvals
            $this->command->info('Phase 4: Clearing approvals...');
            $this->clearApprovals();

            // Phase 5: Clear Main Tables
            $this->command->info('Phase 5: Clearing main tables...');
            $this->clearMainTables();

            // Phase 6: Reset ProductChild (Inventory)
            $this->command->info('Phase 6: Resetting product children inventory status...');
            $this->resetProductChildren();

            // Re-enable foreign key checks
            Schema::enableForeignKeyConstraints();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();
            $this->command->error('Error during reset: ' . $e->getMessage());
            throw $e;
        }

        // Phase 7: Reset Auto-Increment IDs
        // Note: This runs outside the transaction because ALTER TABLE causes implicit commit in MySQL
        $this->command->info('Phase 7: Resetting auto-increment IDs...');
        $this->resetAutoIncrementIds();

        $this->command->info('Module reset completed successfully!');
        $this->displaySummary();
    }

    /**
     * Phase 1: Clear Pivot/Junction Tables
     */
    private function clearPivotTables(): void
    {
        $tables = [
            // Task-related pivots
            'user_task',
            'task_services',
            'task_milestone_inventories',
            'task_milestone',

            // Production-related pivots
            'production_request_materials',
            'sale_production_requests',
            'production_milestone_materials',

            // Sale-Inventory pivots
            'sale_product_children',
            'delivery_order_product_children',

            // Invoice/Billing pivots
            'return_products',
            'credit_note_e_invoice',
            'debit_note_e_invoice',
            'credit_note_con_e_invoice',
            'debit_note_con_e_invoice',
            'consolidated_e_invoice_invoice',
            'billing_invoice',
            'billing_sale_product',
        ];

        $this->deleteTables($tables);
    }

    /**
     * Phase 2: Clear Child/Detail Tables
     */
    private function clearChildTables(): void
    {
        $tables = [
            // Sale child tables
            'sale_payment_amounts',
            'sale_product_accessories',
            'sale_product_warranty_periods',
            'sale_adhoc_services',
            'sale_products',

            // Delivery order child tables
            'delivery_order_product_accessories',
            'delivery_order_adhoc_services',
            'delivery_order_products',
        ];

        $this->deleteTables($tables);
    }

    /**
     * Phase 3: Clear E-Invoice Related Tables
     */
    private function clearEInvoiceTables(): void
    {
        $tables = [
            'credit_notes',
            'debit_notes',
            'e_invoices',
            'consolidated_e_invoices',
            'draft_e_invoices',
        ];

        $this->deleteTables($tables);
    }

    /**
     * Phase 4: Clear Approvals (Entire table)
     */
    private function clearApprovals(): void
    {
        $tables = ['approvals'];
        $this->deleteTables($tables);
    }

    /**
     * Phase 5: Clear Main Tables (including soft-deleted records)
     */
    private function clearMainTables(): void
    {
        $tables = [
            'invoices',
            'billings',
            'delivery_orders',
            'sales',
            'sale_enquiries',
            'tasks',
            'production_requests',
        ];

        $this->deleteTables($tables);
    }

    /**
     * Phase 6: Reset ProductChild (Inventory) status
     */
    private function resetProductChildren(): void
    {
        if (Schema::hasTable('product_children')) {
            $count = DB::table('product_children')
                ->whereNotNull('status')
                ->orWhereNotNull('stock_out_to_type')
                ->count();

            DB::table('product_children')->update([
                'status' => null,
                'stock_out_to_type' => null,
                'stock_out_to_id' => null,
                'stock_out_by' => null,
                'stock_out_at' => null,
                'transfer_by' => null,
                'transferred_from' => null,
                'reject_reason' => null,
            ]);

            $this->command->line("  - product_children: {$count} records reset to available status");
        }
    }

    /**
     * Phase 7: Reset Auto-Increment IDs to 1 for all cleared tables
     */
    private function resetAutoIncrementIds(): void
    {
        $tablesToResetId = [
            // Main tables
            'sales',
            'sale_enquiries',
            'delivery_orders',
            'invoices',
            'billings',
            'tasks',
            'production_requests',
            'approvals',

            // E-invoice tables
            'credit_notes',
            'debit_notes',
            'e_invoices',
            'consolidated_e_invoices',
            'draft_e_invoices',

            // Child/detail tables
            'sale_products',
            'sale_payment_amounts',
            'sale_product_accessories',
            'sale_product_warranty_periods',
            'sale_adhoc_services',
            'delivery_order_products',
            'delivery_order_product_accessories',
            'delivery_order_adhoc_services',

            // Pivot tables with auto-increment (if any)
            'task_milestone',
            'return_products',
        ];

        foreach ($tablesToResetId as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                $this->command->line("  - {$table}: auto-increment reset to 1");
            } else {
                $this->command->line("  - {$table}: table does not exist (skipped)");
            }
        }
    }

    /**
     * Delete all records from the given tables
     */
    private function deleteTables(array $tables): void
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                DB::table($table)->delete();
                $this->command->line("  - {$table}: {$count} records deleted");
            } else {
                $this->command->line("  - {$table}: table does not exist (skipped)");
            }
        }
    }

    /**
     * Display summary of current record counts after reset
     */
    private function displaySummary(): void
    {
        $this->command->newLine();
        $this->command->info('Verification - Current record counts:');

        $tables = [
            'sales',
            'delivery_orders',
            'invoices',
            'tasks',
            'approvals',
            'production_requests',
            'sale_enquiries',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $this->command->line("  - {$table}: {$count}");
            }
        }

        // Check product_children with non-null status
        if (Schema::hasTable('product_children')) {
            $count = DB::table('product_children')->whereNotNull('status')->count();
            $this->command->line("  - product_children (with status): {$count}");
        }

        $this->command->newLine();
        $this->command->info('Auto-increment verification (next ID for key tables):');

        $keyTables = ['sales', 'delivery_orders', 'invoices', 'tasks'];
        foreach ($keyTables as $table) {
            if (Schema::hasTable($table)) {
                $result = DB::select("SHOW TABLE STATUS WHERE Name = ?", [$table]);
                $autoIncrement = $result[0]->Auto_increment ?? 'N/A';
                $this->command->line("  - {$table}: {$autoIncrement}");
            }
        }
    }
}
