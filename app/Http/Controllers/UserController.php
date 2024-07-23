<?php

namespace App\Http\Controllers;

use App\Exports\UserExport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    const ACTIVITY_LOG_NAME = 'users';

    public function index() {
        return view('user_management.list');
    }

    public function getData(Request $request) {
        $records = User::with('roles')->orderBy('id', 'desc');

        if ($request->has('keyword') && $request->input('keyword') != '') {
            $records = $records->where('name', 'like', '%'.$request->input('keyword').'%');
        }

        $records = $records->get();

        $data = [];
        foreach ($records as $key => $record) {
            $data[] = [
                'no' => ($key + 1),
                'name' => $record->name,
                'email' => $record->email,
                'role' => $record->getRoleNames()[0] ?? null,
                'id' => $record->id
            ];
        }

        return $data;
    }

    public function create() {
        $roles = Role::get();

        return view('user_management.create', [
            'roles' => $roles
        ]);
    }

    public function store(Request $request) {
        $role_ids = Role::pluck('id')->toArray();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:250',
            'email' => 'required|string|email|max:250|unique:users',
            'password' => 'required|confirmed',
            'role' => ['required', Rule::in($role_ids)],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        // Create user
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            $selected_role = Role::where('id', $request->input('role'))->first();
            $user->assignRole($selected_role->id);

            // Activity Log
            $log_event = 'create';
            $log_properties = [
                'new_data' => $user,
            ];

            activity(self::ACTIVITY_LOG_NAME)
            ->by(auth()->user())
            ->withProperties($log_properties)
            ->event($log_event)
            ->log($log_event);

            DB::commit();

            return redirect()->route('user_management.index')->with('success', 'User created.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info($th->getMessage());
            return back()->with('error', 'Failed to create the user.');
        }
    }

    public function edit(User $user) {
        $roles = Role::get();

        return view('user_management.edit', [
            'selected_user' => $user,
            'selected_role_id' => $user->load('roles')->roles[0]->id ?? null,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user) {
        $role_ids = Role::pluck('id')->toArray();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:250',
            'password' => 'nullable|confirmed',
            'role' => ['required', Rule::in($role_ids)],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        // Update user
        try {
            DB::beginTransaction();

            $old_user = clone($user);
            $old_user->roles = $old_user->roles;

            $selected_role = Role::where('id', $request->input('role'))->first();

            $user->name = $request->input('name') ?? $user->name;
            $user->password = $request->input('password') != null ? Hash::make($request->input('password')) : $user->password;
            $user->save();
            $user->syncRoles([$selected_role->id]);

            // Activity Log
            $log_event = 'update';
            $log_properties = [
                'old_data' => $old_user,
                'new_data' => $user,
            ];

            activity(self::ACTIVITY_LOG_NAME)
            ->by(auth()->user())
            ->withProperties($log_properties)
            ->event($log_event)
            ->log($log_event);

            DB::commit();

            return redirect()->route('user_management.index')->with('success', 'User updated.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info($th->getMessage());
            return back()->with('error', 'Failed to update the user.');
        }
    }

    public function delete(User $user) {
        // Delete user
        try {
            DB::beginTransaction();

            // Activity Log
            $log_event = 'delete';
            $log_properties = [
                'new_data' => $user,
            ];

            activity(self::ACTIVITY_LOG_NAME)
            ->by(auth()->user())
            ->withProperties($log_properties)
            ->event($log_event)
            ->log($log_event);

            $user->syncRoles([]);
            $user->delete();

            DB::commit();

            return redirect()->route('user_management.index')->with('success', 'User deleted.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info($th->getMessage());
            return back()->with('error', 'Failed to delete the user.');
        }
    }
    
    public function export() {
        return Excel::download(new UserExport, 'users.xlsx');
    }
}
