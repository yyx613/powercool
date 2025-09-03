<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'notification.view',
            'notification.production_complete_notification',

            'approval.view',
            'approval.production_material_transfer_request',
            'approval.type_quotation',
            'approval.type_sale_order',
            'approval.type_delivery_order',
            'approval.type_customer',

            'dashboard.view',

            'inventory.summary.view',

            'inventory.view_action',

            'inventory.category.view',
            'inventory.category.create',
            'inventory.category.edit',
            'inventory.category.delete',

            'inventory.product.view',
            'inventory.product.create',
            'inventory.product.edit',
            'inventory.product.delete',

            'inventory.raw_material.view',
            'inventory.raw_material.create',
            'inventory.raw_material.edit',
            'inventory.raw_material.delete',

            'inventory.raw_material_request.view',

            'grn.view',
            'grn.create',
            'grn.edit',

            'service_reminder.view',
            'service_reminder.create',
            'service_reminder.edit',
            'service_reminder.receive_reminder',

            'service_history.view',

            'warranty.view',

            'sale.quotation.view',
            'sale.quotation.create',
            'sale.quotation.edit',
            'sale.quotation.view_record',
            'sale.quotation.delete',
            'sale.quotation.convert',

            'sale.sale_order.view',
            'sale.sale_order.create',
            'sale.sale_order.edit',
            'sale.sale_order.view_record',
            'sale.sale_order.cancel',
            'sale.sale_order.delete',
            'sale.sale_order.convert',

            'sale.delivery_order.view',
            'sale.delivery_order.convert',

            'sale.transport_acknowledgement.view',
            'sale.transport_acknowledgement.create',

            'sale.invoice.view',
            'sale.invoice.sync_to_autocount',
            'sale.invoice.convert_to_billing',
            'sale.invoice.submit_draft_e_invoice',
            'sale.invoice.submit_consolidated_e_invoice',

            'sale.draft_e_invoice.view',
            'sale.e_invoice.view',

            'sale.target.view',
            'sale.target.convert',

            'sale.billing.view',

            'sale.invoice_return.view',
            'sale.invoice_return.return',

            'task_driver.view',
            'task_driver.create',
            'task_driver.edit',
            'task_driver.delete',

            'task_technician.view',
            'task_technician.create',
            'task_technician.edit',
            'task_technician.delete',

            'task_sale.view',
            'task_sale.create',
            'task_sale.edit',
            'task_sale.delete',

            'production.view',
            'production.create',
            'production.edit',
            'production.delete',
            'production.export_excel',

            'production_material.view',

            'production_request.view',

            'ticket.view',
            'ticket.create',
            'ticket.edit',
            'ticket.delete',

            'customer.view',
            'customer.create',
            'customer.edit',
            'customer.delete',

            'supplier.view',
            'supplier.create',
            'supplier.edit',
            'supplier.delete',

            'dealer.view',
            'dealer.create',
            'dealer.edit',
            'dealer.delete',

            'agent_debtor.view',

            'vehicle.view',
            'vehicle_service.reminder',

            'report.view',

            'user_role_management.view',

            'setting.view',
        ];
        $now = now();

        for ($i = 0; $i < count($permissions); $i++) {
            $permissions_to_insert[] = [
                'name' => $permissions[$i],
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        Permission::insert($permissions_to_insert);
    }
}
