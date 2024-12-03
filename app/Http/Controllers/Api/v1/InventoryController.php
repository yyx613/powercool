<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Sale;
use App\Models\SaleOrderCancellationHistory;
use App\Models\SaleProduct;
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
        $histories = SaleOrderCancellationHistory::select('*', DB::raw('SUM(qty) AS sum_qty'))
            ->with('product.image')
            ->where('sale_person_id', $req->user()->id)
            ->groupBy('product_id');

        if ($req->keyword != null && $req->keyword != '') {
            $histories = $histories->whereHas('product', function($q) use ($req) {
                $q->where('model_name', 'like', '%'.$req->keyword.'%')
                    ->orWhere('sku', 'like', '%'.$req->keyword.'%');
            });
        }

        $histories = $histories->simplePaginate();

        return Response::json([
            'histories' => $histories,
        ], HttpFoundationResponse::HTTP_OK);
    }
}
