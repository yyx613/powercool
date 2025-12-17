<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProduct;
use App\Models\DeliveryOrderProductChild;
use App\Models\FactoryRawMaterial;
use App\Models\Invoice;
use App\Models\MaterialUse;
use App\Models\MaterialUseProduct;
use App\Models\ObjectCreditTerm;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Production;
use App\Models\Sale;
use App\Models\SalePaymentAmount;
use App\Models\SaleProduct;
use App\Models\SalesAgent;
use App\Models\Scopes\ApprovedScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        3 => 'Customer',
        4 => 'Payment Record',
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

        $types = self::TYPES;
        if (!hasPermission('approval.type_quotation')) {
            unset($types[0]);
        }
        if (!hasPermission('approval.type_sale_order')) {
            unset($types[1]);
        }
        if (!hasPermission('approval.type_delivery_order')) {
            unset($types[2]);
        }
        if (!hasPermission('approval.type_customer')) {
            unset($types[3]);
        }
        if (!hasPermission('approval.type_payment_record')) {
            unset($types[4]);
        }

        return view('approval.list', [
            'statuses' => self::STATUSES,
            'types' => $types,
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
        $records = $records->where(function ($q) {
            if (!hasPermission('approval.type_quotation')) {
                $q->orWhere(function ($q) {
                    $q->where('object_type', Sale::class)->whereNot('data', 'like', '%is_quo%');
                });
            }
            if (!hasPermission('approval.type_sale_order')) {
                $q->orWhere(function ($q) {
                    $q->where('object_type', Sale::class)->where('data', 'like', '%is_quo%');
                });
            }
            if (!hasPermission('approval.type_delivery_order')) {
                $q->orWhere(function ($q) {
                    $q->whereNot('object_type', DeliveryOrder::class);
                });
            }
            if (!hasPermission('approval.type_customer')) {
                $q->orWhere(function ($q) {
                    $q->whereNot('object_type', Customer::class);
                });
            }
            if (!hasPermission('approval.type_payment_record')) {
                $q->orWhere(function ($q) {
                    $q->whereNot('object_type', SalePaymentAmount::class);
                });
            }
        });
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
            } elseif ($request->type == 3) {
                $records = $records->where('object_type', Customer::class);
                Session::put('approval-type', $request->type);
            } elseif ($request->type == 4) {
                $records = $records->where('object_type', SalePaymentAmount::class);
                Session::put('approval-type', $request->type);
            }
        } else if (Session::get('approval-type') != null) {
            if (Session::get('approval-type') == 0) {
                $records = $records->where('object_type', Sale::class)->where('data', 'like', '%is_quo%');
            } elseif (Session::get('approval-type') == 1) {
                $records = $records->where('object_type', Sale::class)->whereNot('data', 'like', '%is_quo%');
            } elseif (Session::get('approval-type') == 2) {
                $records = $records->where('object_type', DeliveryOrder::class);
            } elseif (Session::get('approval-type') == 3) {
                $records = $records->where('object_type', Customer::class);
            } elseif (Session::get('approval-type') == 4) {
                $records = $records->where('object_type', SalePaymentAmount::class);
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
            $payload = $record->data == null ? null : json_decode($record->data);
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
                } elseif (get_class($obj) == SalePaymentAmount::class) {
                    $view_url = route('sale_order.edit_payment', ['sale' => $obj->sale_id]);
                }
            }

            $remark = null;
            $cancellation_charge = null;
            if ($payload != null) {
                if (isset($payload->cancellation_remark)) {
                    $remark = $payload->cancellation_remark;
                }
                if (isset($payload->charge)) {
                    $cancellation_charge = $payload->charge;
                }
            }
            // Sale agent name
            $sale_agents = [];
            if (get_class($obj) == Sale::class) {
                $sale_agents[] = $obj->saleperson->name;
            } else if (get_class($obj) == Customer::class && $payload != null && isset($payload->sale_agent_ids)) {
                $sale_agents = SalesAgent::whereIn('id', explode(',', $payload->sale_agent_ids))->pluck('name')->toArray(); 
            }

            // Determine object_sku
            $object_sku = null;
            if ($obj != null) {
                if (get_class($obj) == SalePaymentAmount::class) {
                    $object_sku = $payload->sale_sku ?? null;
                } else {
                    $object_sku = $obj->sku;
                }
            }

            $data['data'][] = [
                'no' => ($key + 1),
                'id' => $record->id,
                'type' => $obj == null ? null : get_class($obj),
                'object_sku' => $object_sku,
                'date' => $record->created_at,
                'data' => $record->data == null ? null : json_decode($record->data),
                'pending_approval' => $record->status == Approval::STATUS_PENDING_APPROVAL,
                'view_url' => $view_url,
                'status' => $record->status,
                'description' => $record->data == null ? null : (json_decode($record->data)->description ?? null),
                'remark' => $remark,
                'cancellation_charge' => $cancellation_charge,
                'debtor_code' => get_class($obj) == Sale::class ? $obj->customer->sku : null,
                'debtor_name' => get_class($obj) == Sale::class ? $obj->customer->company_name : null,
                'sales_agent_name' => count($sale_agents) > 0 ? join(', ', $sale_agents) : null,
                'can_view' => in_array(get_class($obj), [Production::class, FactoryRawMaterial::class, ProductChild::class, MaterialUse::class, Customer::class, SalePaymentAmount::class]) ? false : $record->status != Approval::STATUS_REJECTED
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

                if (isset($data->is_cancellation) && isset($data->is_quo)) {
                    if ($data->is_quo == true) {
                        $obj->status = Sale::STATUS_CANCELLED;
                        $obj->save();
                    } else {
                        (new SaleController)->cancelSaleOrderFlow($obj, false, $data->charge);
                        // // Change converted QUO back to active
                        $quo = Sale::where('convert_to', $obj->id)->first();
                        $quo->status = $quo->hasApprovalAndAllApproved() ? Sale::STATUS_APPROVAL_APPROVED : Sale::STATUS_ACTIVE;
                        $quo->save();
                    }
                } else if (isset($data->is_reuse)) {
                    $obj->status = $obj->hasApprovalAndAllApproved() ? Sale::STATUS_APPROVAL_APPROVED : Sale::STATUS_ACTIVE;
                    $obj->save();
                } else if (isset($data->is_payment_method)) {
                    $obj->status = Sale::STATUS_APPROVAL_APPROVED;
                    $obj->payment_method_status = Sale::STATUS_APPROVAL_APPROVED;
                    $obj->save();
                } else {
                    if (isset($data->sale_product_id)) {
                        SaleProduct::where('id', $data->sale_product_id)->update([
                            'status' => SaleProduct::STATUS_APPROVAL_APPROVED
                        ]);
                    }
                    $obj->status = Sale::STATUS_APPROVAL_APPROVED;
                    $obj->save();

                    if (!Approval::where('object_type', Sale::class)->where('object_id', $obj->id)->where('status', Approval::STATUS_PENDING_APPROVAL)->exists()) {
                        $has_rejected = SaleProduct::where('sale_id', $obj->id)->where('status', SaleProduct::STATUS_APPROVAL_REJECTED)->exists();

                        $obj->status = $has_rejected ? Sale::STATUS_APPROVAL_REJECTED : Sale::STATUS_APPROVAL_APPROVED;
                        $obj->save();
                    }
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
                Product::where('id', $obj->product_id)->increment('qty', $data->qty);

                $obj->save();
            }
            // Product Child 
            if (get_class($obj) == ProductChild::class) {
                $obj->status = ProductChild::STATUS_WAREHOUSE_STOCK_OUT;
                $obj->save();
            }
            // Complete Production 
            if (get_class($obj) == Production::class) {
                $obj->status = Production::STATUS_COMPLETED;
                $obj->save();
            }
            // Customer (credit term) 
            if (get_class($obj) == Customer::class) {
                $data = json_decode($approval->data);

                if (isset($data->is_delete) && isset($data->customer_id)) {
                    Customer::where('id', $data->customer_id)->delete();
                } else {
                    ObjectCreditTerm::where('object_type', Customer::class)->where('object_id', $obj->id)->delete();

                    if ($data->to_credit_term_ids != null) {
                        $terms = [];
                        for ($i = 0; $i < count($data->to_credit_term_ids); $i++) {
                            $terms[] = [
                                'object_type' => Customer::class,
                                'object_id' => $obj->id,
                                'credit_term_id' => $data->to_credit_term_ids[$i],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                        ObjectCreditTerm::insert($terms);
                    }

                    $obj->status = Customer::STATUS_APPROVAL_APPROVED;
                    $obj->save();
                }
            }
            // Payment Record (SalePaymentAmount)
            if (get_class($obj) == SalePaymentAmount::class) {
                $data = json_decode($approval->data);

                if (isset($data->is_payment_edit)) {
                    // Apply the proposed changes
                    $obj->payment_method = $data->proposed->payment_method;
                    $obj->payment_term = $data->proposed->payment_term;
                    $obj->amount = $data->proposed->amount;
                    $obj->date = $data->proposed->date;
                    $obj->reference_number = $data->proposed->reference_number;
                    $obj->approval_status = SalePaymentAmount::STATUS_ACTIVE;
                    $obj->save();
                } elseif (isset($data->is_payment_delete)) {
                    // Soft delete the payment record
                    $obj->delete();
                }

                // Recalculate sale payment status
                $this->recalculateSalePaymentStatus($data->sale_id);
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

    public function reject(Request $req, Approval $approval)
    {
        try {
            DB::beginTransaction();

            $obj = $approval->object()->withoutGlobalScope(ApprovedScope::class)->first();

            $approval->status = Approval::STATUS_REJECTED;
            $approval->reject_remark = $req->remark;
            $approval->save();

            // QUO/SO/DO
            if (get_class($obj) == DeliveryOrder::class) {
                $sale_orders = Sale::whereRaw('find_in_set(' . $obj->id . ', convert_to)')->get();

                for ($i = 0; $i < count($sale_orders); $i++) {
                    $sale_orders[$i]->status = $sale_orders[$i]->hasApprovalAndAllApproved() ? Sale::STATUS_APPROVAL_APPROVED : Sale::STATUS_ACTIVE;

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

                if (isset($data->is_cancellation)) {
                    $obj->status = $obj->hasApprovalAndAllApproved() ? Sale::STATUS_APPROVAL_APPROVED : Sale::STATUS_ACTIVE;
                    $obj->save();
                } else if (isset($data->is_reuse)) {
                    $obj->status = Sale::STATUS_CANCELLED;
                    $obj->save();
                } else if (isset($data->is_payment_method)) {
                    $obj->status = Sale::STATUS_APPROVAL_REJECTED;
                    $obj->payment_method_status = Sale::STATUS_APPROVAL_REJECTED;
                    $obj->save();
                } else {
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
            }
            // Check approval count
            $pending_approval_count = Approval::where('status', Approval::STATUS_PENDING_APPROVAL)->count();
            Cache::put('unread_approval_count', $pending_approval_count);

            // Production Material Transfer Request
            if (get_class($obj) == FactoryRawMaterial::class) {
                $data = json_decode($approval->data);
                $obj->to_warehouse_qty -= $data->qty;
                $obj->status = FactoryRawMaterial::STATUS_APPROVAL_REJECTED;
                $obj->save();
            }
            // Product Child 
            if (get_class($obj) == ProductChild::class) {
                $obj->status = ProductChild::STATUS_TRANSFER_REJECTED;
                $obj->save();
            }
            // Complete Production 
            if (get_class($obj) == Production::class) {
                $data = json_decode($approval->data);
                if (isset($data->type) && $data->type == 'r&d') {
                    $obj->status = Production::STATUS_REJECTED;
                } else {
                    $obj->status = Production::STATUS_DOING;
                }
                $obj->save();
            }
            // Sale Production Request 
            if (get_class($obj) == MaterialUse::class) {
                MaterialUseProduct::where('material_use_id', $obj->id)->delete();
                MaterialUse::where('id', $obj->id)->delete();
            }
            // Customer (credit term) do nothing
            if (get_class($obj) == Customer::class) {
                $data = json_decode($approval->data);

                if (isset($data->is_delete) && isset($data->customer_id)) {
                } else {
                    $obj->status = Customer::STATUS_APPROVAL_REJECTED;
                    $obj->save();
                }
            }
            // Payment Record (SalePaymentAmount) - reset status back to active
            if (get_class($obj) == SalePaymentAmount::class) {
                $obj->approval_status = SalePaymentAmount::STATUS_ACTIVE;
                $obj->save();
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

    public function hasPending()
    {
        try {
            $has_pending_approval = Approval::where('status', Approval::STATUS_PENDING_APPROVAL)->exists();

            return Response::json([
                'has_pending' => $has_pending_approval,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function recalculateSalePaymentStatus($sale_id)
    {
        $sale = Sale::find($sale_id);
        if ($sale == null) {
            return;
        }

        $to_be_paid_amount = $sale->getTotalAmount();
        $paid_amount = $sale->getPaidAmount();

        $status = Sale::PAYMENT_STATUS_UNPAID;
        if ($paid_amount >= $to_be_paid_amount) {
            $status = Sale::PAYMENT_STATUS_PAID;
        } elseif ($paid_amount > 0 && $paid_amount < $to_be_paid_amount) {
            $status = Sale::PAYMENT_STATUS_PARTIALLY_PAID;
        }

        $sale->payment_status = $status;
        $sale->save();
    }
}
