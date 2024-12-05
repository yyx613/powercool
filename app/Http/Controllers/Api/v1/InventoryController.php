<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\SaleOrderCancellation;
use App\Models\TaskMilestoneInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Response;

class InventoryController extends Controller
{
    public function getRawMaterialAndSparepart(Request $req) {
        $raw_materials = Product::where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', false)->get();
        // Sparepart
        $involved_pc_ids = getInvolvedProductChild();

        // Exclude current sale, if edit
        if ($req->task_ms != null) {
            $current_tmi = TaskMilestoneInventory::where('task_milestone_id', $req->task_ms)
                ->where('inventory_type', ProductChild::class)
                ->pluck('inventory_id')
                ->toArray();

            $involved_pc_ids = array_diff($involved_pc_ids, $current_tmi);
        }

        $spareparts = Product::with(['children' => function ($q) use ($involved_pc_ids) {
                $q->whereNull('status')->whereNotIn('id', $involved_pc_ids);
            }])
            ->where('type', Product::TYPE_RAW_MATERIAL)
            ->where('is_sparepart', true)
            ->get();

        return Response::json([
            'raw_materials' => $raw_materials,
            'spareparts' => $spareparts,
        ], HttpFoundationResponse::HTTP_OK);
    }

    public function getSalePersonCancelledProducts(Request $req) {
        $qty_to_on_hold = DB::table('sale_order_cancellation')
                    ->select('id', 'product_id', DB::raw('SUM(qty - COALESCE(extra, 0)) AS qty'))
                    ->where('saleperson_id', $req->user()->id)
                    ->whereNotNull('on_hold_sale_id')
                    ->whereNull('deleted_at')
                    ->groupBy('product_id');

        $qty_to_sell = DB::table('sale_order_cancellation')
                    ->select('id', 'product_id', DB::raw('SUM(qty) AS qty'))
                    ->where('saleperson_id', $req->user()->id)
                    ->whereNull('on_hold_sale_id')
                    ->whereNull('deleted_at')
                    ->groupBy('product_id');

        $cancellation = DB::table('sale_order_cancellation')
                    ->select('sale_order_cancellation.product_id', DB::raw('(qty_to_sell.qty - qty_to_on_hold.qty) AS qty'))
                    ->joinSub($qty_to_sell, 'qty_to_sell', function ($join) {
                        $join->on('sale_order_cancellation.product_id', '=', 'qty_to_sell.product_id');
                    })
                    ->joinSub($qty_to_on_hold, 'qty_to_on_hold', function ($join) {
                        $join->on('sale_order_cancellation.product_id', '=', 'qty_to_on_hold.product_id');
                    })
                    ->join('products', 'products.id', '=', 'sale_order_cancellation.product_id')
                    ->where('saleperson_id', $req->user()->id)
                    ->whereNull('on_hold_sale_id')
                    ->whereNull('sale_order_cancellation.deleted_at')
                    ->having('qty', '>', 0)
                    ->groupBy('sale_order_cancellation.product_id');
        
        // Filter keyword
        if ($req->keyword != null && $req->keyword != '') {
            $cancellation = $cancellation->where('products.model_name', 'like', '%'.$req->keyword.'%')
                ->orWhere('products.sku', 'like', '%'.$req->keyword.'%');
        }

        $cancellation = $cancellation->simplePaginate();

        $cancellation->each(function($q) {
            $product = Product::withTrashed()->with('image')->where('id', $q->product_id)->first();;
            $q->product = $product;
        });

        return Response::json([
            'cancellation' => $cancellation,
        ], HttpFoundationResponse::HTTP_OK);
    }
}
