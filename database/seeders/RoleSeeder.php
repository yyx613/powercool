<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

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
        ];

        for ($i=0; $i < count($roles); $i++) { 
            $roles[$i]['guard_name'] = 'web';
            
            Role::create($roles[$i]);
        }
    }
}
