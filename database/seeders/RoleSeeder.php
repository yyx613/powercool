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
                'id' => Role::PRODUCTION_STAFF,
                'name' => 'Production Staff',
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
