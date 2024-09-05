<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'logsheet.view',
            'logsheet.create',
            'logsheet.edit',
            'quotation.view',
            'quotation.create',
            'quotation.edit',
            'quotation.delete',
            'consignment_note.view',
            'consignment_note.create',
            'consignment_note.edit',
            'consignment_note.delete',
            'consignment_note.attach_logsheet',
            'warehouse.view',
            'warehouse.check_in',
            'warehouse.check_out',
            'warehouse.generate_invoice',
        ];
        
        $master_data_sub = [
            'customers', 'delivery_special_prices', 'miscellaneous_special_prices', 'cargoes', 'lorries',
            'lorry_services', 'drivers', 'helpers', 'measurements', 'sgd_to_myr_rate', 'warehouse_locations',
            'warehouse_charges', 'warehouse_optional_services', 'others'
        ];
        for ($i=0; $i < count($master_data_sub); $i++) { 
            $permissions = [
                'master_data.'.$master_data_sub[$i].'.view',
                'master_data.'.$master_data_sub[$i].'.create',
                'master_data.'.$master_data_sub[$i].'.edit',
                'master_data.'.$master_data_sub[$i].'.delete',
            ];
            if (in_array($master_data_sub[$i], ['delivery_special_prices', 'miscellaneous_special_prices'])) {
                unset($permissions[2]);
            } else if ($master_data_sub[$i] == 'others') {
                unset($permissions[1], $permissions[3]);
            }
            $roles = array_merge($roles, $permissions);
        }
        $roles = array_merge($roles, [
            'finance',
            'invoice',
            'activity_logs',
            'user_management',
            'role_management',
        ]);

        for ($i=0; $i < count($roles); $i++) { 
            Permission::create(['name' => $roles[$i]]);
        }
    }
}
