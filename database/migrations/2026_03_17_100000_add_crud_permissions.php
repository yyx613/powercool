<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'user_role_management.create',
            'user_role_management.edit',
            'user_role_management.delete',
            'setting.edit',
            'vehicle.create',
            'vehicle.edit',
            'service_history.create',
            'production_request.create',
            'warranty.create',
            'agent_debtor.create',
            'agent_debtor.edit',
            'agent_debtor.delete',
        ];

        foreach ($permissions as $permission) {
            $exists = DB::table('permissions')->where('name', $permission)->where('guard_name', 'web')->exists();

            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $permission,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Clear Spatie permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'user_role_management.create',
            'user_role_management.edit',
            'user_role_management.delete',
            'setting.edit',
            'vehicle.create',
            'vehicle.edit',
            'service_history.create',
            'production_request.create',
            'warranty.create',
            'agent_debtor.create',
            'agent_debtor.edit',
            'agent_debtor.delete',
        ])->where('guard_name', 'web')->delete();
    }
};
