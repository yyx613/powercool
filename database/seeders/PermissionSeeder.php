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
            'inventory.summary.view',

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

            'grn.view',
            'grn.create',
            'grn.edit',

            'service_history.view',
            'service_history.create',
            'service_history.edit',
            'service_history.receive_reminder',

            'warranty.view',

            'sale.quotation.view',
            'sale.quotation.create',
            'sale.quotation.edit',
            'sale.quotation.delete',
            'sale.quotation.convert',

            'sale.sale_order.view',
            'sale.sale_order.create',
            'sale.sale_order.edit',
            'sale.sale_order.cancel',
            'sale.sale_order.delete',
            'sale.sale_order.convert',

            'sale.delivery_order.view',
            'sale.delivery_order.convert',

            'sale.invoice.view',

            'sale.target.view',
            'sale.target.convert',

            'sale.billing.view',
            'sale.billing.convert',

            'task.view',
            'task.create',
            'task.edit',
            'task.delete',

            'production.view',
            'production.create',
            'production.edit',
            'production.delete',

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

            'setting.view'
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
