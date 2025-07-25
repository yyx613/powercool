<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as ModelsRole;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'id' => Role::SUPERADMIN,
                'name' => 'Super Admin',
            ],
            [
                'id' => Role::SALE,
                'name' => 'Sale',
            ],
            [
                'id' => Role::TECHNICIAN,
                'name' => 'Technician',
            ],
            [
                'id' => Role::DRIVER,
                'name' => 'Driver',
            ],
            [
                'id' => Role::PRODUCTION_WORKER,
                'name' => 'Production Worker',
            ],
            [
                'id' => Role::PRODUCTION_SUPERVISOR,
                'name' => 'Production Supervisor',
            ],
            [
                'id' => Role::PRODUCTION_ASSISTANT,
                'name' => 'Production Assistant',
            ],
            [
                'id' => Role::WAREHOUSE,
                'name' => 'Warehouse',
            ],
            [
                'id' => Role::FINANCE,
                'name' => 'Finance',
            ],
            [
                'id' => Role::SALE_COORDINATOR,
                'name' => 'Sale Coordinator',
            ],
        ];
        $permissions = Permission::get();

        for ($i = 0; $i < count($roles); $i++) {
            $roles[$i]['guard_name'] = 'web';

            $role = ModelsRole::create($roles[$i]);

            // Assign permissions for Super Admin
            if ($i == 0) {
                for ($j = 0; $j < count($permissions); $j++) {
                    $role->givePermissionTo($permissions[$j]);
                }
            }
        }
    }
}
