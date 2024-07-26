<?php

namespace App\Http\Controllers;

use App\Exports\RoleExport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;

class RoleController extends Controller
{
    public function index() {
        return view('role_management.list');
    }

    public function getData(Request $request) {
        $records = Role::orderBy('id', 'desc');

        if ($request->has('keyword') && $request->input('keyword') != '') {
            $records = $records->where('name', 'like', '%'.$request->input('keyword').'%');
        }

        $records = $records->get();

        $data = [];
        foreach ($records as $key => $record) {
            $data[] = [
                'no' => ($key + 1),
                'role' => $record->name,
                'user_count_under_role' => User::withWhereHas('roles', function ($query) use ($record) {
                    $query->where('id', $record->id);
                })->count(),
                'id' => $record->id
            ];
        }

        return $data;
    }

    public function create() {
        return view('role_management.form');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:250|unique:roles',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $request->input('name')
            ]);
            
            $selected_permissions = $request->except(['_token', 'name']);
            $role->syncPermissions(array_keys($selected_permissions));

            DB::commit();

            return redirect()->route('role_management.index')->with('success', 'Role created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Failed to create the role.');
        }
    }

    public function edit(Role $role) {
        $role_permissions = $role->getAllPermissions()->pluck('name')->toArray();

        return view('role_management.form', [
            'role' => $role,
            'role_permissions' => $role_permissions,
        ]);
    }

    public function update(Request $request, Role $role) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:250|unique:roles,name,' . $role->id,
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        // Update role
        try {
            DB::beginTransaction();

            $selected_permissions = $request->except(['_token', 'name']);

            $old_role = clone($role);
            $old_role->permissions = $old_role->getAllPermissions()->pluck('name')->toArray();

            $role->name = $request->input('name');
            $role->save();
            $role->syncPermissions(array_values($selected_permissions));
            $role->permissions = $role->getAllPermissions()->pluck('name')->toArray();

            // Activity Log
            $log_event = 'update';
            $log_properties = [
                'old_data' => $old_role,
                'new_data' => $role,
            ];

            activity(self::ACTIVITY_LOG_NAME)
            ->by(auth()->user())
            ->withProperties($log_properties)
            ->event($log_event)
            ->log($log_event);
            
            DB::commit();

            return redirect()->route('role_management.index')->with('success', 'Role updated.');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);
            return back()->with('error', 'Failed to update the role.');
        }
    }

    public function delete(Role $role) {
        // Delete role
        try {
            DB::beginTransaction();

            $user_count_under_role = User::with('roles')->get()->filter(
                fn ($user) => $user->roles->where('name', $role->name)->toArray()
            )->count();

            if ($user_count_under_role > 0) {
                return back()->with('warning', 'Please make sure there is no user with ' . $role->name . ' role. Currently, there ' . ($user_count_under_role == 1 ? 'is 1 user' : 'are ' . $user_count_under_role . ' users') . ' under this role.');
            }

            // Activity Log
            $log_event = 'delete';
            $log_properties = [
                'new_data' => $role,
            ];

            activity(self::ACTIVITY_LOG_NAME)
            ->by(auth()->user())
            ->withProperties($log_properties)
            ->event($log_event)
            ->log($log_event);

            $role->syncPermissions([]);
            $role->delete();

            DB::commit();

            return redirect()->route('role_management.index')->with('success', 'Role deleted.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info($th->getMessage());
            return back()->with('error', 'Failed to delete the role.');
        }
    }

    public function export() {
        return Excel::download(new RoleExport, 'roles.xlsx');
    }
}
