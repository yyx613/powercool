<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProduct;
use App\Models\DeliveryOrderProductChild;
use App\Models\FactoryRawMaterial;
use App\Models\Invoice;
use App\Models\MaterialUse;
use App\Models\MaterialUseProduct;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Production;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductionRequest;
use App\Models\Scopes\ApprovedScope;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ApprovalController extends Controller
{
    const STATUSES = [
        0 => 'Pending Approval',
        1 => 'Approved',
        2 => 'Rejected',
    ];
    const TYPES = [
        0 => 'Quotation',
        1 => 'Sale Order',
        2 => 'Delivery Order',
    ];

    public function index()
    {
        if (Session::get('approval-status') != null) {
            $status = Session::get('approval-status');
        }
        if (Session::get('approval-type') != null) {
            $type = Session::get('approval-type');
        }
        $page = Session::get('approval-page');

        return view('approval.list', [
            'statuses' => self::STATUSES,
            'types' => self::TYPES,
            'default_status' => $status ?? null,
            'default_type' => $type ?? null,
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $request)
    {
        Session::put('approval-page', $request->page);

        $records = Approval::latest();

        // Search
        if ($request->has('search') && $request->search['value'] != null) {
            $keyword = $request->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->whereHasMorph('object', [Sale::class, DeliveryOrder::class], function ($query) use ($keyword) {
                    $query->where('sku', 'like', '%' . $keyword . '%');
                });
            });
        }
        // Filter status
        if ($request->has('status')) {
            if ($request->status == null) {
                Session::remove('approval-status');
            } else if ($request->status == 0) {
                $records = $records->where('status', Approval::STATUS_PENDING_APPROVAL);
                Session::put('approval-status', $request->status);
            } elseif ($request->status == 1) {
                $records = $records->where('status', Approval::STATUS_APPROVED);
                Session::put('approval-status', $request->status);
            } elseif ($request->status == 2) {
                $records = $records->where('status', Approval::STATUS_REJECTED);
                Session::put('approval-status', $request->status);
            }
        } else if (Session::get('approval-status') != null) {
            if (Session::get('approval-status') == 0) {
                $records = $records->where('status', Approval::STATUS_PENDING_APPROVAL);
            } elseif (Session::get('approval-status') == 1) {
                $records = $records->where('status', Approval::STATUS_APPROVED);
            } elseif (Session::get('approval-status') == 2) {
                $records = $records->where('status', Approval::STATUS_REJECTED);
            }
        }
        // Filter type
        if ($request->has('type')) {
            if ($request->type == null) {
                Session::remove('approval-type');
            } else if ($request->type == 0) {
                $records = $records->where('object_type', Sale::class)->where('data', 'like', '%is_quo%');
                Session::put('approval-type', $request->type);
            } elseif ($request->type == 1) {
                $records = $records->where('object_type', Sale::class)->whereNot('data', 'like', '%is_quo%');
                Session::put('approval-type', $request->type);
            } elseif ($request->type == 2) {
                $records = $records->where('object_type', DeliveryOrder::class);
                Session::put('approval-type', $request->type);
            }
        } else if (Session::get('approval-type') != null) {
            if (Session::get('approval-type') == 0) {
                $records = $records->where('object_type', Sale::class)->where('data', 'like', '%is_quo%');
            } elseif (Session::get('approval-type') == 1) {
                $records = $records->where('object_type', Sale::class)->whereNot('data', 'like', '%is_quo%');
            } elseif (Session::get('approval-type') == 2) {
                $records = $records->where('object_type', DeliveryOrder::class);
            }
        }

        $has = hasPermission('approval.production_material_transfer_request');
        if (!$has) {
            $records = $records->whereNot('object_type', FactoryRawMaterial::class)->whereNot('object_type', ProductChild::class);
        }

        $records_count = $records->count();
        $records_ids = $records->pluck('id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $obj = $record->object()->withoutGlobalScope(ApprovedScope::class)->withTrashed()->first();
            $view_url = null;
            if ($obj != null) {
                if (get_class($obj) == DeliveryOrder::class) {
                    $view_url = route('delivery_order.index', ['sku' => $obj->sku]);
                } elseif (get_class($obj) == Sale::class) {
                    if ($obj->type == Sale::TYPE_QUO) {
                        $view_url = route('quotation.index', ['sku' => $obj->sku]);
                    } else {
                        $view_url = route('sale_order.index', ['sku' => $obj->sku]);
                    }
                }
            }

            $data['data'][] = [
                'no' => ($key + 1),
                'id' => $record->id,
                'type' => $obj == null ? null : get_class($obj),
                'object_sku' => $obj == null ? null : $obj->sku,
                'date' => $record->created_at,
                'data' => $record->data == null ? null : json_decode($record->data),
                'pending_approval' => $record->status == Approval::STATUS_PENDING_APPROVAL,
                'view_url' => $view_url,
                'status' => $record->status,
                'description' => $record->data == null ? null : (json_decode($record->data)->description ?? null),
                'can_view' => in_array(get_class($obj), [Production::class, FactoryRawMaterial::class, ProductChild::class, MaterialUse::class]) ? false : $record->status != Approval::STATUS_REJECTED
            ];
        }

        return response()->json($data);
    }

    public function approve(Approval $approval)
    {
        try {
            DB::beginTransaction();

            $obj = $approval->object()->withoutGlobalScope(ApprovedScope::class)->first();

            $approval->status = Approval::STATUS_APPROVED;
            $approval->save();
            // Check approval count
            $pending_approval_count = Approval::where('status', Approval::STATUS_PENDING_APPROVAL)->count();
            Cache::put('unread_approval_count', $pending_approval_count);
            // Update respective QUO/SO/DO
            if (get_class($approval->object) == Sale::class) {
                $data = json_decode($approval->data);
                if (isset($data->sale_product_id)) {
                    SaleProduct::where('id', $data->sale_product_id)->update([
                        'status' => SaleProduct::STATUS_APPROVAL_APPROVED
                    ]);
                }
                if (!Approval::where('object_type', Sale::class)->where('object_id', $approval->object->id)->where('status', Approval::STATUS_PENDING_APPROVAL)->exists()) {
                    $has_rejected = Approval::where('object_type', Sale::class)->where('object_id', $approval->object->id)->where('status', Approval::STATUS_REJECTED)->exists();

                    $approval->object->status = $has_rejected ? Sale::STATUS_APPROVAL_REJECTED : Sale::STATUS_APPROVAL_APPROVED;
                    $approval->object->save();
                }
            } else if (get_class($approval->object) == DeliveryOrder::class) {
                $approval->object->status = DeliveryOrder::STATUS_APPROVAL_APPROVED;
                $approval->object->save();
            }

            // Production Material Transfer Request
            if (get_class($obj) == FactoryRawMaterial::class) {
                $data = json_decode($approval->data);
                $obj->qty -= $data->qty;
                $obj->to_warehouse_qty -= $data->qty;

                $obj->save();
            }
            // Product Child 
            if (get_class($obj) == ProductChild::class) {
                $obj->status = ProductChild::STATUS_TRANSFER_APPROVED;
                $obj->save();
            }
            // Complete Production 
            if (get_class($obj) == Production::class) {
                $obj->status = Production::STATUS_COMPLETED;
                $obj->save();
            }

            DB::commit();

            return Response::json([
                'result' => true,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function reject(Approval $approval)
    {
        try {
            DB::beginTransaction();

            $obj = $approval->object()->withoutGlobalScope(ApprovedScope::class)->first();

            $approval->status = Approval::STATUS_REJECTED;
            $approval->save();

            // QUO/SO/DO
            if (get_class($obj) == DeliveryOrder::class) {
                $sale_orders = Sale::whereRaw('find_in_set(' . $obj->id . ', convert_to)')->get();

                for ($i = 0; $i < count($sale_orders); $i++) {
                    $sale_orders[$i]->status = Sale::STATUS_ACTIVE;

                    $current_do_ids = explode(',', $sale_orders[$i]->convert_to);
                    array_splice($current_do_ids, array_search($obj->id, $current_do_ids), 1);
                    $sale_orders[$i]->convert_to = count($current_do_ids) == 0 ? null : implode(',', $current_do_ids);

                    $sale_orders[$i]->save();

                    $dop_ids = DeliveryOrderProduct::where('delivery_order_id', $obj->id)->pluck('id');
                    DeliveryOrderProductChild::whereIn('delivery_order_product_id', $dop_ids)->delete();
                    DeliveryOrderProduct::whereIn('id', $dop_ids)->delete();
                    Invoice::where('id', $obj->invoice_id)->delete();

                    $obj->delete();
                }
            } elseif (get_class($obj) == Sale::class) {
                $data = json_decode($approval->data);
                if (isset($data->sale_product_id)) {
                    SaleProduct::where('id', $data->sale_product_id)->update([
                        'status' => SaleProduct::STATUS_APPROVAL_REJECTED
                    ]);
                }
                if (!Approval::where('object_type', Sale::class)->where('object_id', $obj->id)->where('status', Approval::STATUS_PENDING_APPROVAL)->exists()) {
                    $obj->status = Sale::STATUS_APPROVAL_REJECTED;
                    $obj->save();
                }
            }
            // Check approval count
            $pending_approval_count = Approval::where('status', Approval::STATUS_PENDING_APPROVAL)->count();
            Cache::put('unread_approval_count', $pending_approval_count);
            // Update respective QUO/SO/DO
            // if (get_class($obj) == Sale::class) {
            //     $obj->status = Sale::STATUS_APPROVAL_REJECTED;
            //     $obj->save();
            // } else if (get_class($obj) == DeliveryOrder::class) {
            //     $obj->status = DeliveryOrder::STATUS_APPROVAL_REJECTED;
            //     $obj->save();
            // }

            // Production Material Transfer Request
            if (get_class($obj) == FactoryRawMaterial::class) {
                $data = json_decode($approval->data);
                $obj->to_warehouse_qty -= $data->qty;
                $obj->save();
            }
            // Product Child 
            if (get_class($obj) == ProductChild::class) {
                $obj->status = ProductChild::STATUS_STOCK_OUT;
                $obj->save();
            }
            // Complete Production 
            if (get_class($obj) == Production::class) {
                $obj->status = Production::STATUS_DOING;
                $obj->save();
            }
            // Sale Production Request 
            if (get_class($obj) == MaterialUse::class) {
                MaterialUseProduct::where('material_use_id', $obj->id)->delete();
                MaterialUse::where('id', $obj->id)->delete();
            }

            DB::commit();

            return Response::json([
                'result' => true,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            report($th);
            DB::rollBack();

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function stockIn(Approval $approval)
    {
        try {
            DB::beginTransaction();

            $obj = $approval->object()->withoutGlobalScope(ApprovedScope::class)->first();

            if (get_class($obj) == FactoryRawMaterial::class) {
                $data = json_decode($approval->data);
                Product::where('id', $obj->product_id)->increment('qty', $data->qty);
            }
            $approval->status = Approval::STATUS_APPROVED_ACTION_DONE;
            $approval->save();

            DB::commit();

            return back()->with('success', 'Stocked In');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }
}
