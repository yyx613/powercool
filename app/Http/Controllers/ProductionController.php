<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\Production;
use App\Models\ProductionMilestone;
use App\Models\ProductionProduct;
use App\Models\UserProduction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ProductionController extends Controller
{
    protected $prod;
    protected $userProd;
    protected $ms;
    protected $prodMs;

    public function __construct() {
        $this->prod = new Production;
        $this->userProd = new UserProduction;
        $this->ms = new Milestone;
        $this->prodMs = new ProductionMilestone;
    }

    public function index() {
        return view('production.list', [
            'productin_left' => $this->prod::where('due_date', now()->format('Y-m-d'))->count(),
            'to_do' => $this->prod::where('status', $this->prod::STATUS_TO_DO)->count(), 
            'doing' => $this->prod::where('status', $this->prod::STATUS_DOING)->count(),
            'completed' => $this->prod::where('status', $this->prod::STATUS_COMPLETED)->count(),
        ]);
    }

    public function getData(Request $req) {
        $records = $this->prod;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->orWhere('start_date', 'like', '%' . $keyword . '%')
                    ->orWhere('due_date', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'name',
                2 => 'start_date',
                3 => 'due_date',
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
                'sku' => $record->sku,
                'name' => $record->name,
                'start_date' => $record->start_date,
                'due_date' => $record->due_date,
                'days_left' => Carbon::parse($record->due_date)->addDay()->diffInDays(now()),
                'progress' => $record->getProgress($record),
                'status' => $record->status,
            ];
        }

        return response()->json($data);
    }

    public function create() {
        return view('production.form');
    }

    public function edit(Production $production) {
        $production->load('users', 'milestones');

        return view('production.form', [
            'production' => $production,
        ]);
    }
    
    public function view(Production $production) {
        $production->load('users', 'milestones');
        $production->formatted_created_at = Carbon::parse($production->created_at)->format('d M Y');
        $production->status = ($this->prod)->statusToHumanRead($production->status);
        $production->progress = ($this->prod)->getProgress($production);
        
        return view('production.view', [
            'production' => $production,
        ]);
    }

    public function delete(Production $production) {
        $production->delete();

        return back()->with('success', 'Production deleted');
    }

    public function upsert(Request $req, Production $production) {
        $rules = [
            'name' => 'required|max:250',
            'desc' => 'required|max:250',
            'remark' => 'nullable|max:250',
            'start_date' => 'required',
            'due_date' => 'required',
            'status' => 'required',
            'product' => 'required',
            'order' => 'required',
            'assign' => 'required',
            'assign.*' => 'exists:users,id',
            'milestone' => 'required_without:custom_milestone',
            'custom_milestone' => 'required_without:milestone',
        ];
        // Validate request
        $req->validate($rules, [], [
            'desc' => 'description',
        ]);

        try {
            DB::beginTransaction();

            if ($production->id == null) {
                $production = $this->prod::create([
                    'sku' => $this->prod->generateSku(),
                    'product_id' => $req->product,
                    'sale_id' => $req->order,
                    'name' => $req->name,
                    'desc' => $req->desc,
                    'remark' => $req->remark,
                    'start_date' => $req->start_date,
                    'due_date' => $req->due_date,
                    'status' => $req->status,
                ]);

                if ($req->qty != null) {
                    $data = [];
                    $existing_skus = [];
                    for ($i=0; $i < $req->qty; $i++) { 
                        $sku = ($this->prodChild)->generateSku($prod->sku, $existing_skus);
                        $data[] = [
                            'product_id' => $prod->id,
                            'sku' => $sku,
                            'location' => $this->prodChild::LOCATION_WAREHOUSE,
                            'created_at' => $prod->created_at,
                            'updated_at' => $prod->updated_at,
                        ];
                        $existing_skus[] = $sku;
                    }
                    $this->prodChild->insert($data);
                }
            } else {
                $production->update([
                    'product_id' => $req->product,
                    'sale_id' => $req->order,
                    'name' => $req->name,
                    'desc' => $req->desc,
                    'remark' => $req->remark,
                    'start_date' => $req->start_date,
                    'due_date' => $req->due_date,
                    'status' => $req->status,
                ]);
            }
            // Assign
            $this->userProd::where('production_id', $production->id)->whereNotIn('user_id', $req->assign ?? [])->delete();
            foreach ($req->assign as $assign_id) {
                $up = $this->userProd::where('production_id', $production->id)->where('user_id', $assign_id)->first();
                if ($up == null) {
                    $this->userProd::create([
                        'user_id' => $assign_id,
                        'production_id' => $production->id
                    ]);
                }
            }

            $this->prodMs::where('production_id', $production->id)->whereNotIn('milestone_id', $req->milestone ?? [])->delete();
            // Create milestone
            if ($req->milestone != null) {
                foreach ($req->milestone as $ms_id) {
                    $ms = $this->prodMs::where('production_id', $production->id)->where('milestone_id', $ms_id)->first();
                    if ($ms == null) {
                        $this->prodMs::create([
                            'production_id' => $production->id,
                            'milestone_id' => $ms_id,
                        ]);
                    }
                }
            }
            // Create custom milestones
            if ($req->custom_milestone != null) {
                foreach ($req->custom_milestone as $ms) {
                    $custom_ms = $this->ms::create([
                        'type' => $this->ms::TYPE_PRODUCTION,
                        'name' => $ms,
                        'is_custom' => true,
                    ]);
                    $this->prodMs::create([
                        'production_id' => $production->id,
                        'milestone_id' => $custom_ms->id,
                    ]);
                }
            }

            DB::commit();

            return redirect(route('production.index'))->with('success', 'Production created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function checkInMilestone(Request $req) {
        $rules = [
            'production_milestone_id' => 'required',
            'datetime' => 'required',
            // 'assign' => 'required',
            // 'assign.*' => 'exists:users,id',
        ];
        // Validate request
        $req->validate($rules);

        try {
            DB::beginTransaction();

            $pm = $this->prodMs::where('id', $req->production_milestone_id)->first();
            if ($pm->submitted_at == null) {
                $pm->submitted_at = Carbon::parse($req->datetime)->format('Y-m-d H:i:s');
                $pm->save();
            }

            $prod = $this->prod::where('id', $pm->production_id)->first();
            if ($this->prod->getProgress($prod) >= 100) {
                $prod->status = $this->prod::STATUS_COMPLETED;
                $prod->save();
            } else if ($this->prod->getProgress($prod) > 0) {
                $prod->status = $this->prod::STATUS_DOING;
                $prod->save();
            }

            DB::commit();

            return Response::json([
                'result' => true,
                'status' => $this->prod->statusToHumanRead($prod->status),
                'progress' => $this->prod->getProgress($prod),
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
