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
            'notification.view_mobile_app',
            'notification.view_service_reminder',
            'notification.view_vehicle_service_reminder',
            'notification.view_production_completed',

            'approval.view',
            'approval.production_material_transfer_request',
            'approval.type_quotation',
            'approval.type_sale_order',
            'approval.type_delivery_order',
            'approval.type_customer',
            'approval.type_payment_record',
            'approval.type_raw_material_request',
            'approval.type_complete_production_request',
            'approval.type_grn',

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

            'inventory.customize.view',
            'inventory.customize.edit',

            'inventory.raw_material_request.view',
            'inventory.raw_material_request.create',
            'inventory.raw_material_request.complete',

            'adhoc_service.view',
            'adhoc_service.create',
            'adhoc_service.edit',
            'adhoc_service.delete',

            'grn.view',
            'grn.create',
            'grn.edit',
            'grn.cancel',
            'grn.delete',

            'service_reminder.view',
            'service_reminder.create',
            'service_reminder.edit',
            'service_reminder.receive_reminder',

            'service_history.view',
            'service_history.create',

            'warranty.view',
            'warranty.create',

            'service_form.view',
            'service_form.create',
            'service_form.edit',
            'service_form.delete',

            'sale_enquiry.view',
            'sale_enquiry.create',
            'sale_enquiry.edit',
            'sale_enquiry.delete',

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
            'sale.sale_order.convert_from',
            'sale.sale_order.convert_to',
            'sale.sale_order.billing',

            'sale.cash_sale.view',
            'sale.cash_sale.create',

            'sale.delivery_order.view',
            'sale.delivery_order.convert',

            'sale.transport_acknowledgement.view',
            'sale.transport_acknowledgement.create',

            'sale.invoice.view',
            'sale.invoice.convert_from_so',
            'sale.invoice.sync_to_autocount',
            'sale.invoice.convert_to_billing',
            'sale.invoice.submit_draft_e_invoice',
            'sale.invoice.submit_consolidated_e_invoice',

            'sale.draft_e_invoice.view',
            'sale.e_invoice.view',

            'sale.target.view',
            'sale.target.create',
            'sale.target.edit',

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
            'production_request.create',
            'production_request.complete',
            'production.cancel',
            'production.complete',
            'production.force_complete',
            'production.check_in_milestone',

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
            'dealer.edit',
            'dealer.delete',

            'agent_debtor.view',
            'agent_debtor.edit',
            'agent_debtor.delete',

            'vehicle.view',
            'vehicle.create',
            'vehicle.edit',
            'vehicle_service.reminder',

            'e_order.view',

            'report.production',
            'report.sales',
            'report.stock',
            'report.earning',
            'report.service',
            'report.technician_stock',

            'user_role_management.view',
            'user_role_management.create',
            'user_role_management.edit',
            'user_role_management.delete',

            'setting.area.view',
            'setting.material_use.view',
            'setting.country.view',
            'setting.credit_term.view',
            'setting.currency.view',
            'setting.debtor_type.view',
            'setting.factory.view',
            'setting.milestone.view',
            'setting.payment_method.view',
            'setting.inventory_type.view',
            'setting.promotion.view',
            'setting.state.view',
            'setting.project_type.view',
            'setting.platform.view',
            'setting.priority.view',
            'setting.sales_agent.view',
            'setting.service.view',
            'setting.tax_rate.view',
            'setting.sync.view',
            'setting.uom.view',
            'setting.warranty_period.view',
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
