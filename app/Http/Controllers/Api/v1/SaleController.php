<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Target;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Response;

class SaleController extends Controller
{
    public function getAllSalesTarget(Request $req) {
        $user = $req->user();

        try {
            $st = Target::where('sale_id', $user->id)->orderBy('date', 'desc')->simplePaginate();

            $st->each(function($q) use ($user) {
                $q->current_amount = Sale::where('type', Sale::TYPE_SO)
                    ->where('sale_id', $user->id)
                    ->where('created_at', 'like', '%'.Carbon::parse($q->date)->format('Y-m').'%')
                    ->sum('payment_amount');
            });

            return Response::json([
                'sales_targets' => $st,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            report($th);

            return Response::json([
                'msg' => 'something went wrong'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
