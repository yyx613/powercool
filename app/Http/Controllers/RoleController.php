<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $page = Session::get('role-management-page');

        return view('role_management.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $request)
    {
        $records = Role::orderBy('id', 'desc');

        Session::put('role-management-page', $request->page);

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
                'id' => $record->id,
            ];
        }

        return $data;
    }

    public function create()
    {
        return view('role_management.form');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:250|unique:roles',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        // Create role
        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $request->input('name'),
            ]);

            $selected_permissions = $request->except(['_token', 'name']);

            $role->name = $request->input('name');
            $role->save();
            $role->syncPermissions(array_values($selected_permissions));

            DB::commit();

            return redirect()->route('role_management.index')->with('success', 'Role created.');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Failed to create the role.');
        }
    }

    public function edit(Role $role)
    {
        $role_permissions = $role->getAllPermissions()->pluck('name')->toArray();

        return view('role_management.form', [
            'role' => $role,
            'role_permissions' => $role_permissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:250|unique:roles,name,'.$role->id,
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        // Update role
        try {
            DB::beginTransaction();

            $selected_permissions = $request->except(['_token', 'name']);

            $role->name = $request->input('name');
            $role->save();
            $role->syncPermissions(array_values($selected_permissions));
            $role->permissions = $role->getAllPermissions()->pluck('name')->toArray();

            DB::commit();

            return redirect()->route('role_management.index')->with('success', 'Role updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Failed to update the role.');
        }
    }
}
