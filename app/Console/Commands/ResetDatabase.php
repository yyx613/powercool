<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ResetDatabase extends Command
{
    protected $signature = 'app:reset-database {--force : Skip confirmation prompt} {--dry-run : Show what would be cleared without executing}';

    protected $description = 'Reset database by clearing all records except debtor, inventory, raw material, product type, suppliers, sales agents, users/roles, and framework tables';

    /**
     * Tables to preserve (not cleared).
     */
    private const PRESERVED_TABLES = [
        // Debtor
        'customers', 'debtor_types', 'agent_debtors', 'customer_locations',
        'customer_credit_terms', 'customer_sales_agents', 'dealers',
        // Inventory / Products
        'products', 'product_children', 'product_costs', 'product_milestones',
        'product_selling_prices', 'inventory_categories', 'billing_products',
        // Raw Material
        'factory_raw_materials', 'factory_raw_material_records',
        // Product Type
        'inventory_types',
        // Milestones
        'milestones',
        // Suppliers & Sales Agents
        'suppliers', 'sales_agents',
        // Branches
        'branches',
        // Users & Roles
        'users', 'roles', 'permissions', 'model_has_permissions', 'model_has_roles', 'role_has_permissions',
        // Laravel Framework
        'migrations', 'failed_jobs', 'password_reset_tokens', 'personal_access_tokens',
    ];

    /**
     * Tables to clear, grouped by phase for safe deletion order.
     */
    private const PHASES = [
        'Phase 1: Pivot/Junction Tables' => [
            'user_task', 'task_services', 'task_milestone_inventories', 'task_milestone',
            'user_production', 'production_request_materials', 'sale_production_requests',
            'production_milestone_materials', 'production_milestone_materials_preview',
            'production_milestone_rejects', 'sale_product_children', 'delivery_order_product_children',
            'return_products', 'credit_note_e_invoice', 'debit_note_e_invoice',
            'credit_note_con_e_invoice', 'debit_note_con_e_invoice',
            'credit_note_consolidated_e_invoice', 'debit_note_consolidated_e_invoice',
            'consolidated_e_invoice_invoice', 'billing_invoice', 'billing_sale_product',
            'sales_sales_agents', 'classification_code_product',
            'material_use_products', 'material_uses_products',
        ],
        'Phase 2: Child/Detail Tables' => [
            'sale_payment_amounts', 'sale_product_accessories', 'sale_product_warranty_periods',
            'sale_adhoc_services', 'sale_products', 'delivery_order_product_accessories',
            'delivery_order_adhoc_services', 'delivery_order_products',
            'transport_acknowledgement_products', 'quotation_products',
            'service_form_product_warranty_periods', 'service_form_products',
            'service_form_service_items', 'vehicle_service_items',
            'inventory_service_reminders', 'inventory_service_histories',
            'raw_material_request_material_collected', 'raw_material_request_materials',
            'production_milestones', 'production_due_dates', 'production_milestone',
        ],
        'Phase 3: E-Invoice Tables' => [
            'credit_notes', 'debit_notes', 'e_invoices',
            'consolidated_e_invoices', 'draft_e_invoices',
        ],
        'Phase 4: Secondary Tables' => [
            'approvals', 'attachments', 'object_credit_terms',
            'grn', 'notifications', 'activity_logs',
        ],
        'Phase 5: Config/Reference Tables' => [
            'settings', 'countries', 'states', 'currencies', 'credit_terms',
            'uom', 'msic_codes', 'classification_codes', 'factories',
            'areas', 'payment_methods', 'warranty_periods', 'services',
            'priorities', 'promotions', 'adhoc_services', 'cash_sale_locations',
            'platforms', 'platform_tokens', 'project_types', 'targets', 'tickets',
        ],
        'Phase 6: Main/Parent Tables' => [
            'invoices', 'billings', 'delivery_orders', 'transport_acknowledgements',
            'sale_third_party_addresses', 'sale_order_cancellation', 'sale_enquiries', 'sales',
            'tasks', 'service_forms', 'production_requests', 'productions',
            'raw_material_requests', 'quotations', 'vehicles', 'vehicle', 'vehicle_services',
            'material_uses', 'customize_products',
        ],
    ];

    private array $results = [];

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('This command is disabled in production.');
            return 1;
        }

        if ($this->option('dry-run')) {
            return $this->displayDryRun();
        }

        $this->warn('⚠ DATABASE RESET');
        $this->warn('This will PERMANENTLY DELETE all records in affected tables.');
        $this->warn('Preserved: debtor, inventory, raw material, product type, suppliers, sales agents, users/roles');
        $this->newLine();

        if (! $this->option('force')) {
            $confirmation = $this->ask('Type YES to confirm');
            if ($confirmation !== 'YES') {
                $this->info('Reset cancelled.');
                return 0;
            }
        }

        DB::beginTransaction();

        try {
            Schema::disableForeignKeyConstraints();

            foreach (self::PHASES as $phaseName => $tables) {
                $this->info($phaseName);
                $this->clearTables($tables);
            }

            // Phase 7: Clear branch morph records for cleared tables
            $this->info('Phase 7: Clear branch records for cleared tables');
            $this->clearBranchMorphRecords();

            // Phase 8: Reset Product Children
            $this->info('Phase 8: Reset Product Children');
            $this->resetProductChildren();

            Schema::enableForeignKeyConstraints();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();
            $this->error('Error during reset: ' . $e->getMessage());
            return 1;
        }

        // Phase 9: Clear storage files
        $this->info('Phase 9: Clear storage files');
        $this->clearStorageFiles();

        // Phase 10: Reset Auto-Increment (outside transaction)
        $this->info('Phase 10: Reset Auto-Increment IDs');
        $this->resetAutoIncrementIds();

        // Phase 11: Re-seed reference data
        $this->info('Phase 11: Re-seed reference data');
        $this->runSeeders();

        $this->newLine();
        $this->info('Database reset completed successfully!');
        $this->displaySummary();

        return 0;
    }

    private function clearTables(array $tables): void
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                DB::table($table)->delete();
                $this->results[$table] = $count;
                $this->line("  - {$table}: {$count} records deleted");
            } else {
                $this->results[$table] = -1;
                $this->line("  - {$table}: table does not exist (skipped)");
            }
        }
    }

    /**
     * Models whose object_type appears in the branches table
     * and whose tables are being cleared.
     */
    private const CLEARED_BRANCH_MORPH_TYPES = [
        'App\\Models\\Sale',
        'App\\Models\\SaleEnquiry',
        'App\\Models\\DeliveryOrder',
        'App\\Models\\Invoice',
        'App\\Models\\Billing',
        'App\\Models\\Task',
        'App\\Models\\Approval',
        'App\\Models\\ActivityLog',
        'App\\Models\\ProductionRequest',
        'App\\Models\\Production',
        'App\\Models\\RawMaterialRequest',
        'App\\Models\\ServiceForm',
        'App\\Models\\TransportAcknowledgement',
        'App\\Models\\GRN',
        'App\\Models\\DraftEInvoice',
        'App\\Models\\Vehicle',
        'App\\Models\\VehicleService',
        'App\\Models\\Ticket',
        'App\\Models\\Target',
        'App\\Models\\SaleProduct',
        'App\\Models\\SaleProductionRequest',
        'App\\Models\\MaterialUse',
        'App\\Models\\CustomizeProduct',
        // Config/reference models being cleared
        'App\\Models\\Setting',
        'App\\Models\\Country',
        'App\\Models\\State',
        'App\\Models\\Currency',
        'App\\Models\\CreditTerm',
        'App\\Models\\UOM',
        'App\\Models\\MsicCode',
        'App\\Models\\ClassificationCode',
        'App\\Models\\Factory',
        'App\\Models\\Area',
        'App\\Models\\PaymentMethod',
        'App\\Models\\WarrantyPeriod',
        'App\\Models\\Service',
        'App\\Models\\Priority',
        'App\\Models\\Promotion',
        'App\\Models\\AdhocService',
        'App\\Models\\Platform',
        'App\\Models\\ProjectType',
    ];

    private function clearBranchMorphRecords(): void
    {
        if (Schema::hasTable('branches')) {
            $count = DB::table('branches')
                ->whereIn('object_type', self::CLEARED_BRANCH_MORPH_TYPES)
                ->count();

            DB::table('branches')
                ->whereIn('object_type', self::CLEARED_BRANCH_MORPH_TYPES)
                ->delete();

            $this->results['branches (morph cleanup)'] = $count;
            $this->line("  - branches: {$count} morph records deleted for cleared tables");

            // Show remaining unique object_type values
            $remainingTypes = DB::table('branches')
                ->select('object_type', DB::raw('COUNT(*) as count'))
                ->groupBy('object_type')
                ->orderBy('object_type')
                ->get();

            if ($remainingTypes->isNotEmpty()) {
                $this->line('  - Remaining branch object_type:');
                foreach ($remainingTypes as $type) {
                    $this->line("    - {$type->object_type}: {$type->count}");
                }
            } else {
                $this->line('  - No remaining branch records');
            }

            // Rearrange remaining branch IDs to be contiguous
            $this->rearrangeBranchIds();
        }
    }

    private function rearrangeBranchIds(): void
    {
        $remaining = DB::table('branches')->orderBy('id')->get();

        if ($remaining->isEmpty()) {
            $this->line("  - branches: no remaining records to rearrange");
            return;
        }

        // Temporarily disable FK constraints already handled by parent
        $newId = 1;
        foreach ($remaining as $row) {
            if ($row->id !== $newId) {
                DB::table('branches')->where('id', $row->id)->update(['id' => $newId]);
            }
            $newId++;
        }

        $this->line("  - branches: IDs rearranged (1 to " . ($newId - 1) . ")");
    }

    private function resetProductChildren(): void
    {
        if (Schema::hasTable('product_children')) {
            $count = DB::table('product_children')
                ->where(function ($query) {
                    $query->whereNotNull('status')
                        ->orWhereNotNull('stock_out_to_type');
                })
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

            $this->line("  - product_children: {$count} records reset to available status");
        }
    }

    /**
     * Storage directories to clear (matching cleared tables).
     * Paths relative to storage/app/public/.
     */
    private const STORAGE_DIRS_TO_CLEAR = [
        'delivery_order',
        'invoice',
        'e-invoice',
        'transport_acknowledgement',
        'attachments/production_milestone_reject',
        'attachments/task_milestone_inventory',
        'attachments/task_signature',
    ];

    private function clearStorageFiles(): void
    {
        $basePath = storage_path('app/public');

        foreach (self::STORAGE_DIRS_TO_CLEAR as $dir) {
            $path = $basePath . '/' . $dir;

            if (File::isDirectory($path)) {
                $files = File::allFiles($path);
                $count = count($files);
                File::cleanDirectory($path);
                $this->results["storage/{$dir}"] = $count;
                $this->line("  - {$dir}: {$count} files deleted");
            } else {
                $this->results["storage/{$dir}"] = -1;
                $this->line("  - {$dir}: directory does not exist (skipped)");
            }
        }
    }

    private function resetAutoIncrementIds(): void
    {
        $allTables = collect(self::PHASES)->flatten()->toArray();

        foreach ($allTables as $table) {
            if (Schema::hasTable($table) && ($this->results[$table] ?? -1) >= 0) {
                try {
                    DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
                } catch (\Exception) {
                    // Some tables may not have auto-increment, skip silently
                }
            }
        }

        // Reset branches auto-increment after ID rearrangement (Phase 7)
        if (Schema::hasTable('branches')) {
            $maxId = DB::table('branches')->max('id') ?? 0;
            DB::statement("ALTER TABLE `branches` AUTO_INCREMENT = " . ($maxId + 1));
        }

        $this->line('  Auto-increment reset for all cleared tables.');
    }

    /**
     * Seeders to run after reset, in order.
     */
    private const SEEDERS = [
        \Database\Seeders\InventoryCategorySeeder::class,
        \Database\Seeders\WarrantyPeriodSeeder::class,
        \Database\Seeders\DebtorTypeSeeder::class,
        \Database\Seeders\CurrencySeeder::class,
        \Database\Seeders\CreditTermSeeder::class,
        \Database\Seeders\FactorySeeder::class,
        \Database\Seeders\PlatformSeeder::class,
        \Database\Seeders\PrioritySeeder::class,
        \Database\Seeders\StateCitySeeder::class,
        \Database\Seeders\SparePartPhotoSeeder::class,
    ];

    private function runSeeders(): void
    {
        foreach (self::SEEDERS as $seeder) {
            try {
                $this->call('db:seed', ['--class' => $seeder, '--force' => true]);
                $this->line("  - {$seeder}: seeded");
            } catch (\Exception $e) {
                $this->warn("  - {$seeder}: failed ({$e->getMessage()})");
            }
        }
    }

    private function displayDryRun(): int
    {
        $this->info('DRY RUN — No changes will be made.');
        $this->newLine();

        $this->info('Tables to PRESERVE:');
        foreach (self::PRESERVED_TABLES as $table) {
            $exists = Schema::hasTable($table);
            $count = $exists ? DB::table($table)->count() : 0;
            $status = $exists ? "{$count} records" : 'does not exist';
            $this->line("  ✓ {$table} ({$status})");
        }

        $this->newLine();
        $this->info('Tables to CLEAR:');
        $totalRecords = 0;

        foreach (self::PHASES as $phaseName => $tables) {
            $this->line("  {$phaseName}:");
            foreach ($tables as $table) {
                $exists = Schema::hasTable($table);
                $count = $exists ? DB::table($table)->count() : 0;
                $status = $exists ? "{$count} records" : 'does not exist';
                $this->line("    ✗ {$table} ({$status})");
                $totalRecords += $count;
            }
        }

        $this->newLine();
        $this->info('Storage directories to CLEAR:');
        $basePath = storage_path('app/public');
        $totalFiles = 0;

        foreach (self::STORAGE_DIRS_TO_CLEAR as $dir) {
            $path = $basePath . '/' . $dir;
            if (File::isDirectory($path)) {
                $fileCount = count(File::allFiles($path));
                $this->line("    ✗ {$dir} ({$fileCount} files)");
                $totalFiles += $fileCount;
            } else {
                $this->line("    ✗ {$dir} (does not exist)");
            }
        }

        $this->newLine();
        $this->info("Total records to be cleared: {$totalRecords}");
        $this->info("Total files to be cleared: {$totalFiles}");

        return 0;
    }

    private function displaySummary(): void
    {
        $this->newLine();

        $rows = [];
        $total = 0;

        foreach ($this->results as $table => $count) {
            if ($count >= 0) {
                $rows[] = [$table, number_format($count), 'Cleared'];
                $total += $count;
            } else {
                $rows[] = [$table, '-', 'Skipped'];
            }
        }

        $rows[] = ['TOTAL', number_format($total), ''];

        $this->table(['Table', 'Records Deleted', 'Status'], $rows);
    }
}
