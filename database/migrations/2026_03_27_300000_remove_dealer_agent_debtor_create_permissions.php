<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = ['dealer.create', 'agent_debtor.create'];

        DB::table('permissions')->whereIn('name', $permissions)->where('guard_name', 'web')->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissions = ['dealer.create', 'agent_debtor.create'];

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

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
