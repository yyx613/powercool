<?php

namespace Database\Seeders;

use App\Models\Approval;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleEnquiry;
use App\Models\SaleProduct;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Demo data for verifying the Sale Enquiry "Related Sales" table:
 *  - a management user (sees the related-sales table with the 5 new columns)
 *  - a sales-only user (the table is hidden; only enquiry details show)
 *  - one accepted enquiry assigned to the salesperson with two linked sales
 *    (different amounts / dates / payment statuses)
 *
 * Idempotent: re-running reuses the same users / customer / enquiry / sales.
 * Login for both demo users: password = "password"
 */
class SaleEnquiryRelatedSalesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $location = Branch::LOCATION_KL;

        // --- Permissions / role -------------------------------------------------
        foreach ([
            'sale_enquiry.view', 'sale_enquiry.create', 'sale_enquiry.edit',
            'approval.view', 'approval.type_sale_enquiry',
        ] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Sale role must carry id = Role::SALE so isSalesOnly() recognises it.
        $saleRole = SpatieRole::firstOrCreate(
            ['id' => Role::SALE],
            ['name' => 'Sale', 'guard_name' => 'web']
        );
        $saleRole->givePermissionTo('sale_enquiry.view');

        // --- Users --------------------------------------------------------------
        $manager = User::firstOrCreate(
            ['email' => 'manager.demo@powercool.test'],
            ['name' => 'Demo Manager', 'password' => Hash::make('password'), 'sku' => 'EMP-DEMO-MGR', 'is_active' => 1]
        );
        $manager->givePermissionTo('sale_enquiry.view');
        // Lets the manager see and action the No-Deal approval in the Approval module.
        $manager->givePermissionTo('approval.view');
        $manager->givePermissionTo('approval.type_sale_enquiry');
        $this->assignBranch(User::class, $manager->id, $location);

        $salesperson = User::firstOrCreate(
            ['email' => 'sales.demo@powercool.test'],
            ['name' => 'Demo Salesperson', 'password' => Hash::make('password'), 'sku' => 'EMP-DEMO-SLS', 'is_active' => 1]
        );
        // Sales-only: ensure this is the user's single role.
        $salesperson->syncRoles([$saleRole]);
        $this->assignBranch(User::class, $salesperson->id, $location);

        // --- Customer -----------------------------------------------------------
        $customer = Customer::withoutGlobalScopes()->firstOrCreate(
            ['company_name' => 'Demo Cold Storage Sdn Bhd'],
            ['sku' => '300-DEMO-C01', 'status' => 1, 'for_einvoice' => 0]
        );
        // Backfill the sku on a previously-seeded customer so the "Create Debtor"
        // progress milestone shows a reference.
        if (empty($customer->sku)) {
            $customer->update(['sku' => '300-DEMO-C01']);
        }
        $this->assignBranch(Customer::class, $customer->id, $location);

        // --- Enquiry (accepted, so the salesperson can open it) -----------------
        $enquiry = SaleEnquiry::withoutGlobalScopes()->firstOrCreate(
            ['sku' => 'ENQ-DEMO-0001'],
            [
                'enquiry_date' => now()->subDays(10),
                'enquiry_source' => SaleEnquiry::SOURCE_WEBSITE,
                'name' => 'Demo Cold Storage Sdn Bhd',
                'phone_number' => '0123456789',
                'email' => 'buyer@demo-cold.test',
                'preferred_contact_method' => SaleEnquiry::CONTACT_WHATSAPP,
                'category' => SaleEnquiry::TYPE_PRODUCT_PRICING,
                'description' => 'Interested in two display chillers.',
                'product_service_interested' => 'Display Chiller',
                'assigned_user_id' => $salesperson->id,
                'priority' => SaleEnquiry::PRIORITY_MEDIUM,
                'status' => SaleEnquiry::STATUS_IN_PROGRESS,
                'quality' => SaleEnquiry::QUALITY_SEEN_AND_REPLY,
                'created_by' => $manager->id,
                'accepted_at' => now()->subDays(9),
                'accepted_by' => $salesperson->id,
            ]
        );
        $this->assignBranch(SaleEnquiry::class, $enquiry->id, $location);

        // --- Linked sales: one quotation + two sale orders ----------------------
        // The quotation populates the "Create Quotation No" progress milestone;
        // the sale orders populate "Create SO No" (and the Related Sales table).
        $this->makeSale($enquiry, $customer, 'QUO-DEMO-001', now()->subDays(7), Sale::PAYMENT_STATUS_UNPAID, 2, 1500.00, $location, Sale::TYPE_QUO);
        $this->makeSale($enquiry, $customer, '300-DEMO-001', now()->subDays(5), Sale::PAYMENT_STATUS_PAID, 2, 1500.00, $location);
        $this->makeSale($enquiry, $customer, '300-DEMO-002', now(), Sale::PAYMENT_STATUS_PARTIALLY_PAID, 1, 2500.00, $location);

        // --- Second enquiry with a PENDING No-Deal approval ---------------------
        // Gives the manager something to Approve/Reject in the Approval module,
        // and shows the "No Deal pending approval" badge on the enquiry list.
        $enquiry2 = SaleEnquiry::withoutGlobalScopes()->firstOrCreate(
            ['sku' => 'ENQ-DEMO-0002'],
            [
                'enquiry_date' => now()->subDays(6),
                'enquiry_source' => SaleEnquiry::SOURCE_WHATSAPP,
                'name' => 'Walk-in Buyer (No Deal demo)',
                'phone_number' => '0198887777',
                'email' => 'walkin@demo-cold.test',
                'preferred_contact_method' => SaleEnquiry::CONTACT_WHATSAPP,
                'category' => SaleEnquiry::TYPE_PRODUCT_PRICING,
                'description' => 'Asked for price then went quiet.',
                'product_service_interested' => 'Chest Freezer',
                'assigned_user_id' => $salesperson->id,
                'priority' => SaleEnquiry::PRIORITY_LOW,
                'status' => SaleEnquiry::STATUS_IN_PROGRESS,
                'quality' => SaleEnquiry::QUALITY_SEEN_NO_REPLY,
                'created_by' => $manager->id,
                'accepted_at' => now()->subDays(5),
                'accepted_by' => $salesperson->id,
            ]
        );
        $this->assignBranch(SaleEnquiry::class, $enquiry2->id, $location);

        $approval = Approval::withoutGlobalScopes()->firstOrCreate(
            ['object_type' => SaleEnquiry::class, 'object_id' => $enquiry2->id],
            [
                'status' => Approval::STATUS_PENDING_APPROVAL,
                'data' => json_encode([
                    'is_no_deal' => true,
                    'reason' => 'Customer found a cheaper unit elsewhere.',
                    'description' => $salesperson->name . ' marked enquiry ' . $enquiry2->sku . ' as No Deal.',
                ]),
            ]
        );
        $this->assignBranch(Approval::class, $approval->id, $location);

        $this->command?->info('Demo ready.');
        $this->command?->info('Populated enquiry (timeline): ' . $enquiry->sku . ' (id ' . $enquiry->id . ')');
        $this->command?->info('Pending No-Deal enquiry:      ' . $enquiry2->sku . ' (id ' . $enquiry2->id . ')');
        $this->command?->info('Manager login:     manager.demo@powercool.test / password');
        $this->command?->info('Salesperson login: sales.demo@powercool.test / password');
    }

    private function makeSale($enquiry, $customer, string $sku, $date, int $paymentStatus, int $qty, float $unitPrice, int $location, int $type = Sale::TYPE_SO): void
    {
        $sale = Sale::withoutGlobalScopes()->firstOrCreate(
            ['sku' => $sku],
            [
                'sale_enquiry_id' => $enquiry->id,
                'customer_id' => $customer->id,
                'type' => $type,
                'status' => Sale::STATUS_ACTIVE,
                'payment_status' => $paymentStatus,
                'custom_date' => $date,
                'is_draft' => 0,
                'payment_method_revised' => 0,
                'self_collect' => 0,
            ]
        );
        $this->assignBranch(Sale::class, $sale->id, $location);

        // One product line so getTotalAmount() returns a non-zero RM amount.
        SaleProduct::firstOrCreate(
            ['sale_id' => $sale->id, 'sequence' => 1],
            [
                'product_id' => 1,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'cost' => 0,
                'is_foc' => 0,
                'with_sst' => 0,
                'discount_type' => 'fixed',
                'discount' => 0,
                'sst_amount' => 0,
                'revised' => 0,
            ]
        );
    }

    private function assignBranch(string $type, int $id, int $location): void
    {
        Branch::firstOrCreate([
            'object_type' => $type,
            'object_id' => $id,
            'location' => $location,
        ]);
    }
}
