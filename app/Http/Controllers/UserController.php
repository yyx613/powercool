<?php

namespace App\Http\Controllers;

use App\Exports\UserExport;
use App\Models\Attachment;
use App\Models\Branch;
use App\Models\Role as ModelsRole;
use App\Models\Scopes\BranchScope;
use App\Models\User;
use App\Support\TableSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
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
        'name' => 'required|max:250|unique:users,name',
        'gender' => 'nullable',
        'address' => 'nullable',
        'state' => 'nullable',
        'city' => 'nullable',
        'zip_code' => 'nullable',
        'phone_number' => 'nullable',
        'email' => 'required|email|max:250|unique:users',
        'website' => 'nullable',
        'epf' => 'nullable',
        'car_plate' => 'nullable',
        'status' => 'required',
        'remark' => 'nullable|max:250',
        'password' => 'required|confirmed',
        'branch' => 'required',
        'picture' => 'nullable',
        'picture.*' => 'file|extensions:jpg,png,jpeg',
        'sales_agent' => 'nullable',
        'sales_agent.*' => 'nullable',
    ];

    public function index()
    {
        if (Session::get('user-management-role') != null) {
            $role = Session::get('user-management-role');
        }
        $page = Session::get('user-management-page');

        return view('user_management.list', [
            'roles' => ModelsRole::get(),
            'default_page' => $page ?? null,
            'default_role' => $role ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        $records = User::with('roles');

        Session::put('user-management-page', $req->page);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            // Resolve which branch location codes have a label matching the keyword.
            $branchLabels = [
                Branch::LOCATION_EVERY => (new Branch)->keyToLabel(Branch::LOCATION_EVERY),
                Branch::LOCATION_KL => (new Branch)->keyToLabel(Branch::LOCATION_KL),
                Branch::LOCATION_PENANG => (new Branch)->keyToLabel(Branch::LOCATION_PENANG),
            ];
            $matchedBranchCodes = TableSearch::matchingCodes($branchLabels, $keyword);

            // Keep text columns, role and branch in the SAME where-group so they OR together.
            $records = $records->where(function ($q) use ($keyword, $matchedBranchCodes) {
                foreach (['sku', 'name', 'email'] as $col) {
                    $q->orWhere($col, 'like', '%' . $keyword . '%');
                }

                // Role: Spatie roles relation, matched by role name.
                $q->orWhereHas('roles', fn ($r) => $r->where('name', 'like', '%' . $keyword . '%'));

                // Branch: polymorphic relation; relation definition handles the morph
                // constraints, so only the location code needs filtering here.
                if (! empty($matchedBranchCodes)) {
                    $q->orWhereHas('branch', fn ($b) => $b->whereIn('location', $matchedBranchCodes));
                }
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'name',
                1 => 'email',
                // Role: Spatie roles relation, joined name list. Sort by the
                // concatenated role names so the key matches the displayed value.
                2 => DB::raw('(select group_concat(r.name order by r.id) from model_has_roles mr join roles r on r.id = mr.role_id where mr.model_id = users.id and mr.model_type = ' . DB::getPdo()->quote(User::class) . ')'),
                // Branch: polymorphic morphOne; the displayed label order
                // (Every < Kuala Lumpur < Penang) follows the numeric location code.
                3 => DB::raw('(select b.location from branches b where b.object_id = users.id and b.object_type = ' . DB::getPdo()->quote(User::class) . ' and b.deleted_at is null limit 1)'),
            ];
            foreach ($req->order as $order) {
                if (isset($map[$order['column']])) {
                    $records = $records->orderBy($map[$order['column']], $order['dir']);
                }
            }
        } else {
            $records = $records->orderBy('id', 'desc');
        }

        if ($req->has('role')) {
            if ($req->role == null) {
                Session::remove('user-management-role');
            } else {
                Session::put('user-management-role', $req->role);
            }
        } else if (Session::get('user-management-role') != null) {
            $req->merge(['role' => Session::get('user-management-role')]);
        }

        $all_records = $records->get();

        $data = [
            "data" => [],
        ];

        // Get record counts
        $recordsTotal = 0;
        $records_ids = [];
        $page = ($req->page ?? 1) - 1;
        foreach ($all_records as $key => $record) {
            if ($req->role != null && !in_array($req->role, getUserRoleId($record))) {
                continue;
            }
            $recordsTotal++;

            if ((($key + 1) > $page * 10) && count($data['data']) < 10) {
                $records_ids[] = $record->id;

                $data['data'][] = [
                    'id' => $record->id,
                    'name' => $record->name,
                    'email' => $record->email,
                    'role' => join(', ', getUserRole($record)),
                    'branch' => $record->branch == null ? null : (new Branch)->keyToLabel($record->branch->location),
                ];
            }
        }

        $data["recordsTotal"] = $recordsTotal;
        $data["recordsFiltered"] = $recordsTotal;
        $data["records_ids"] = $records_ids;

        return response()->json($data);
    }

    public function create()
    {
        return view('user_management.form');
    }

    public function store(Request $req)
    {
        $rules = self::FORM_RULES;
        if (in_array(ModelsRole::SUPERADMIN, $req->role)) {
            $rules['branch'] = 'nullable';
        }
        $validator = Validator::make($req->all(), $rules, [], [
            'name' => 'username'
        ]);
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
            $selected_role = Role::whereIn('id', $req->role)->pluck('id')->toArray();
            $user->syncRoles($selected_role);

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
            // Sales agent
            if ($req->sales_agent != null) {
                $sa_data = [];
                DB::table('sales_sales_agents')->where('sales_id', $user->id)->delete();

                for ($i = 0; $i < count($req->sales_agent); $i++) {
                    $sa_data[] = [
                        'sales_id' => $user->id,
                        'sales_agent_id' => $req->sales_agent[$i],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                if (count($sa_data) > 0) {
                    DB::table('sales_sales_agents')->insert($sa_data);
                }
            }

            if (in_array(ModelsRole::SUPERADMIN, $req->role)) {
                Branch::where('object_type', User::class)->where('object_id', $user->id)->delete();
            } else {
                (new Branch)->assign(User::class, $user->id, $req->branch);
            }

            DB::commit();

            return redirect()->route('user_management.index')->with('success', __('User created'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', __('Something went wrong. Please contact administrator'))->withInput();
        }
    }

    public function edit($user)
    {
        if ($user == 1) {
            abort(404);
        }
        $user = User::where('id', $user)->firstOrFail();

        $user->load('pictures', 'salesAgents');

        return view('user_management.form', [
            'user' => $user,
            'user_role_ids' => getUserRoleId($user),
            'user_sales_agents_ids' => $user->salesAgents->pluck('id')->toArray()
        ]);
    }

    public function update(Request $req, User $user)
    {
        $rules = self::FORM_RULES;
        $rules['password'] = 'nullable|confirmed';
        $rules['name'] = 'required|max:250|unique:users,name,' . $user->id;
        if (in_array(ModelsRole::SUPERADMIN, $req->role)) {
            $rules['branch'] = 'nullable';
        }

        unset($rules['email']);
        $validator = Validator::make($req->all(), $rules, [], [
            'picture.*' => 'picture',
            'name' => 'username'
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
            $selected_role = Role::whereIn('id', $req->role)->pluck('id')->toArray();
            $user->syncRoles($selected_role);

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
            // Sales agent
            if ($req->sales_agent != null) {
                $sa_data = [];
                DB::table('sales_sales_agents')->where('sales_id', $user->id)->delete();

                for ($i = 0; $i < count($req->sales_agent); $i++) {
                    $sa_data[] = [
                        'sales_id' => $user->id,
                        'sales_agent_id' => $req->sales_agent[$i],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                if (count($sa_data) > 0) {
                    DB::table('sales_sales_agents')->insert($sa_data);
                }
            }

            if (in_array(ModelsRole::SUPERADMIN, $req->role)) {
                Branch::where('object_type', User::class)->where('object_id', $user->id)->delete();
            } else {
                (new Branch)->assign(User::class, $user->id, $req->branch);
            }

            DB::commit();

            return redirect()->route('user_management.index')->with('success', __('User updated'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', __('Something went wrong. Please contact administrator'))->withInput();
        }
    }

    public function delete(User $user)
    {
        try {
            DB::beginTransaction();

            $user->syncRoles([]);
            $user->delete();

            DB::commit();

            return redirect()->route('user_management.index')->with('success', __('User deleted'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', __('Something went wrong. Please contact administrator'));
        }
    }

    public function asBranch(Request $req)
    {
        Session::put('as_branch', $req->branch);
    }

    public function get($user_id)
    {
        $user = User::withoutGlobalScope(BranchScope::class)->where('id', $user_id)->first();
        return Response::json([
            'user' => $user
        ]);
    }
}
