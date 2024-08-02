<?php

namespace App\Http\Controllers;

use App\Exports\UserExport;
use App\Models\Attachment;
use App\Models\Role as ModelsRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    const FORM_RULES = [
        'department' => 'required|max:250',
        'name' => 'required|max:250',
        'gender' => 'required',
        'address' => 'required',
        'state' => 'nullable',
        'city' => 'nullable',
        'zip_code' => 'nullable',
        'phone_number' => 'required',
        'email' => 'required|email|max:250|unique:users',
        'website' => 'nullable',
        'epf' => 'nullable',
        'status' => 'required',
        'remark' => 'nullable|max:250',
        'password' => 'required|confirmed',
        'picture' => 'nullable',
        'picture.*' => 'file|extensions:jpg,png,jpeg'
    ];

    public function index() {
        return view('user_management.list');
    }

    public function getData(Request $request) {
        $records = User::with('roles')->whereHas('roles', function($q) {
            $q->whereNot('id', ModelsRole::SUPERADMIN);
        })->orderBy('id', 'desc');

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
                'role' => getUserRole($record),
                'id' => $record->id
            ];
        }

        return $data;
    }

    public function create() {
        return view('user_management.form');
    }

    public function store(Request $req) {
        $validator = Validator::make($req->all(), self::FORM_RULES);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'sku' => (new User)->generateSku(),
                'gender' => $req->gender,
                'address' => $req->address,
                'state' => $req->state,
                'city' => $req->city,
                'zip_code' => $req->zip_code,
                'phone_number' => $req->phone_number,
                'website' => $req->website,
                'epf' => $req->epf,
                'is_active' => $req->boolean('status'),
                'remark' => $req->remark,
                'email' => $req->email,
                'name' => $req->name,
                'password' => Hash::make($req->input('password')),
            ]);
            $selected_role = Role::where('id', $req->department)->first();
            $user->assignRole($selected_role->id);

            if ($req->hasFile('picture')) {
                foreach ($req->file('picture') as $key => $file) {
                    $path = Storage::putFile(Attachment::USER_PATH, $file);
                    Attachment::create([
                        'object_type' => User::class,
                        'object_id' => $user->id,
                        'src' => basename($path),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('user_management.index')->with('success', 'User created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);
            
            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(User $user) {
        $user->load('pictures');

        return view('user_management.form', [
            'user' => $user,
            'user_role_id' => $user->load('roles')->roles[0]->id ?? null,
        ]);
    }

    public function update(Request $req, User $user) {
        $rules = self::FORM_RULES;
        $rules['password'] = 'nullable|confirmed';
        
        unset($rules['email']);
        $validator = Validator::make($req->all(), $rules, [], [
            'picture.*' => 'picture'
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $user->update([
                'gender' => $req->gender,
                'address' => $req->address,
                'state' => $req->state,
                'city' => $req->city,
                'zip_code' => $req->zip_code,
                'phone_number' => $req->phone_number,
                'website' => $req->website,
                'epf' => $req->epf,
                'is_active' => $req->boolean('status'),
                'remark' => $req->remark,
                'name' => $req->name,
                'password' => $req->password == null ? $user->password : Hash::make($req->input('password')),
            ]);
            $selected_role = Role::where('id', $req->department)->first();
            $user->syncRoles([$selected_role->id]);

            if ($req->hasFile('picture')) {
                Attachment::where([
                    ['object_type', User::class],
                    ['object_id', $user->id]
                ])->delete();

                foreach ($req->file('picture') as $key => $file) {
                    $path = Storage::putFile(Attachment::USER_PATH, $file);
                    Attachment::create([
                        'object_type' => User::class,
                        'object_id' => $user->id,
                        'src' => basename($path),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('user_management.index')->with('success', 'User updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);
            
            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete(User $user) {
        try {
            DB::beginTransaction();

            $user->syncRoles([]);
            $user->delete();

            DB::commit();

            return redirect()->route('user_management.index')->with('success', 'User deleted');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }
}
