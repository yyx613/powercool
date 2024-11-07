<?php

namespace App\Http\Controllers;

use App\Exports\UserExport;
use App\Models\Attachment;
use App\Models\Branch;
use App\Models\Role as ModelsRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    const FORM_RULES = [
        'role' => 'required',
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
        'car_plate' => 'nullable',
        'status' => 'required',
        'remark' => 'nullable|max:250',
        'password' => 'required|confirmed',
        'branch' => 'required',
        'picture' => 'nullable',
        'picture.*' => 'file|extensions:jpg,png,jpeg'
    ];

    public function index() {
        return view('user_management.list');
    }

    public function getData(Request $req) {
        $records = User::with('roles')->whereHas('roles', function($q) {
            $q->whereNot('id', ModelsRole::SUPERADMIN);
        });

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'name',
                1 => 'email',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('id', 'desc');
        }

        $records_count = $records->count();
        $records_ids = $records->pluck('id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'id' => $record->id,
                'name' => $record->name,
                'email' => $record->email,
                'role' => getUserRole($record),
                'branch' => (new Branch)->keyToLabel($record->branch->location),
            ];
        }

        return response()->json($data);
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
                'car_plate' => $req->car_plate,
                'is_active' => $req->boolean('status'),
                'remark' => $req->remark,
                'email' => $req->email,
                'name' => $req->name,
                'password' => Hash::make($req->input('password')),
            ]);
            $selected_role = Role::where('id', $req->role)->first();
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

            (new Branch)->assign(User::class, $user->id, $req->branch);

            DB::commit();

            return redirect()->route('user_management.index')->with('success', 'User created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);
            
            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit($user) {
        if ($user == 1) {
            abort(404);
        }
        $user = User::where('id', $user)->firstOrFail();

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
                'car_plate' => $req->car_plate,
                'is_active' => $req->boolean('status'),
                'remark' => $req->remark,
                'name' => $req->name,
                'password' => $req->password == null ? $user->password : Hash::make($req->input('password')),
            ]);
            $selected_role = Role::where('id', $req->role)->first();
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

            (new Branch)->assign(User::class, $user->id, $req->branch);
            
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

    public function asBranch(Request $req) {
        Session::put('as_branch', $req->branch);
    }
}
