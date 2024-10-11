<?php

namespace App\Http\Controllers;

use App\Exports\EarningReportExport;
use App\Exports\ProductionReportExport;
use App\Exports\SalesReportExport;
use App\Exports\StockReportExport;
use App\Models\Product;
use App\Models\Production;
use App\Models\ProductionMilestoneMaterial;
use App\Models\Sale;
use App\Models\SaleProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    protected $production;
    protected $product;
    protected $sale;
    protected $saleProduct;
    protected $productionMsMaterial;

    public function __construct() {
        $this->production = new Production;
        $this->product = new Product;
        $this->sale = new Sale;
        $this->saleProduct = new SaleProduct;
        $this->productionMsMaterial = new ProductionMilestoneMaterial();
    }

    public function indexProduction() {
        $this->clearSession();
        return view('report.production_list');
    }

    public function getDataProduction(Request $req) {
        $keyword = null;
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];
        }
        Session::put('report_start_date', $req->start_date);
        Session::put('report_end_date', $req->end_date);
        Session::put('report_keyword', $keyword);
        
        $records = $this->queryProduction($req->start_date, $req->end_date, $keyword);

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
                'product_name' => $record->product->model_name,
                'product_code' => $record->product->sku,
            ];
        }

        return response()->json($data);
    }

    public function exportProduction() {
        $records = $this->queryProduction(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new ProductionReportExport($records), 'production-report.xlsx');
    }

    private function queryProduction(string $start_date, string $end_date, string | null $keyword) {
        $records = $this->production;

        // Daterange
        if ($start_date != 'null' && $end_date != 'null') {
            if ($start_date == $end_date) {
                $records = $records->where('created_at', 'like', '%' . $start_date . '%');
            } else {
                $end_date_next_day = Carbon::parse($end_date)->addDay()->format('Y-m-d');

                $records = $records->where('created_at', '>=', $start_date)->where('created_at', '<=', $end_date_next_day);
            }
        }
        // Search
        if ($keyword != null) {
            $records = $records->where(function ($q) use ($keyword) {
                $q->orWhereHas('product', function ($q) use ($keyword) {
                    return $q->where('model_name', 'like', '%' . $keyword . '%')->orWhere('sku', 'like', '%'.$keyword.'%');
                });
            });
        }
        $records = $records->orderBy('id', 'desc');

        return $records;
    }

    public function indexSales() {
        $this->clearSession();
        return view('report.sales_list');
    }

    public function getDataSales(Request $req) {
        $keyword = null;
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];
        }
        Session::put('report_start_date', $req->start_date);
        Session::put('report_end_date', $req->end_date);
        Session::put('report_keyword', $keyword);
        
        $records = $this->querySales($req->start_date, $req->end_date, $keyword);

        $records_count = $records->count();
        $records_ids = $records->pluck('sales.id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        $overall_qty = 0;
        $overall_amount = 0;
        foreach ($records_paginator as $key => $record) {
            $overall_qty += $record->sum_qty;
            $overall_amount += $record->sum_amount - $record->sum_promo_amount - ($record->payment_amount ?? 0);

            $data['data'][] = [
                'id' => $record->id,
                'salesperson' => $record->saleperson,
                'qty' => $record->sum_qty,
                'promo' => number_format($record->sum_promo_amount, 2),
                'amount' => number_format($record->sum_amount - $record->sum_promo_amount, 2),
                'outstanding_amount' => number_format($record->sum_amount - $record->sum_promo_amount - ($record->payment_amount ?? 0), 2),
                'overall_qty' => $overall_qty,
                'overall_amount' => number_format($overall_amount, 2),
            ];
        }

        return response()->json($data);
    }

    public function exportSales() {
        $records = $this->querySales(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new SalesReportExport($records), 'sales-report.xlsx');
    }

    private function querySales(string $start_date, string $end_date, string | null $keyword) {
        $promo = DB::table('promotions')
            ->select('id', 'amount');

        $amount = DB::table('sale_products')
            ->select(
                'sale_products.sale_id', 'sale_products.promotion_id', DB::raw('SUM(sale_products.qty) as sum_qty'),
                DB::raw('SUM(sale_products.qty * sale_products.unit_price) as sum_amount'),
                DB::raw('SUM(promo.amount) as sum_promo_amount')
            )
            ->groupBy('sale_products.sale_id')
            ->leftJoinSub($promo, 'promo', function ($join) {
                $join->on('sale_products.promotion_id', '=', 'promo.id');
            });

        $records = DB::table('sales')
            ->select(
                'sales.id AS id', 'users.name AS saleperson', DB::raw('SUM(sum_qty) as sum_qty'),
                DB::raw('SUM(sum_amount) as sum_amount'), DB::raw('SUM(sum_promo_amount) as sum_promo_amount'),
                'sales.payment_amount AS payment_amount', 'sales.created_at'
            )
            ->where('sales.type', Sale::TYPE_SO)
            ->join('users', 'users.id', '=', 'sales.sale_id')
            ->joinSub($amount, 'amount', function ($join) {
                $join->on('sales.id', '=', 'amount.sale_id');
            });

        // Daterange
        if ($start_date != 'null' && $end_date != 'null') {
            if ($start_date == $end_date) {
                $records = $records->where('sales.created_at', 'like', '%' . $start_date . '%');
            } else {
                $end_date_next_day = Carbon::parse($end_date)->addDay()->format('Y-m-d');

                $records = $records->where('sales.created_at', '>=', $start_date)->where('sales.created_at', '<=', $end_date_next_day);
            }
        }
        // Search
        if ($keyword != null) {
            $records = $records->where(function ($q) use ($keyword) {
                return $q->where('users.name', 'like', '%' . $keyword . '%');
            });
        }
        $records = $records->groupBy('sales.sale_id')->orderBy('sales.id', 'desc');

        return $records;
    }

    public function indexStock() {
        return view('report.stock_list');
    }

    public function getDataStock(Request $req) {
        $keyword = null;
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];
        }
        Session::put('report_start_date', $req->start_date);
        Session::put('report_end_date', $req->end_date);
        Session::put('report_keyword', $keyword);
        
        $records = $this->queryStock($req->start_date, $req->end_date, $keyword);

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
            $is_raw_material = $record->is_sparepart !== null && $record->is_sparepart == false;

            if ($is_raw_material) {
                $reserved_stock = $this->productionMsMaterial::where('product_id', $record->id)->where('on_hold', false)->sum('qty');
                $on_hold_stock = $this->productionMsMaterial::where('product_id', $record->id)->where('on_hold', true)->sum('qty');
                $available_stock = $record->qty - $reserved_stock - $on_hold_stock;
            }

            $data['data'][] = [
                'id' => $record->id,
                'product_name' => $record->model_name,
                'product_code' => $record->sku,
                'warehouse_available_stock' => $is_raw_material ? $available_stock : $this->product->warehouseAvailableStock($record->id),
                'warehouse_reserved_stock' => $is_raw_material ? $reserved_stock : $this->product->warehouseReservedStock($record->id),
                'warehouse_on_hold_stock' => $is_raw_material ? $on_hold_stock : $this->product->warehouseOnHoldStock($record->id),
            ];
        }

        return response()->json($data);
    }

    public function exportStock() {
        $records = $this->queryStock(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new StockReportExport($records), 'stock-report.xlsx');
    }

    private function queryStock(string $start_date, string $end_date, string | null $keyword) {
        $records = $this->product;

        // Daterange
        if ($start_date != 'null' && $end_date != 'null') {
            if ($start_date == $end_date) {
                $records = $records->where('created_at', 'like', '%' . $start_date . '%');
            } else {
                $end_date_next_day = Carbon::parse($end_date)->addDay()->format('Y-m-d');

                $records = $records->where('created_at', '>=', $start_date)->where('created_at', '<=', $end_date_next_day);
            }
        }
        // Search
        if ($keyword != null) {
            $records = $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%'.$keyword.'%')
                    ->orWhere('model_name', 'like', '%'.$keyword.'%');
            });
        }
        $records = $records->orderBy('id', 'desc');

        return $records;
    }

    public function indexEarning() {
        return view('report.earning_list');
    }

    public function getDataEarning(Request $req) {
        $keyword = null;
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];
        }
        Session::put('report_start_date', $req->start_date);
        Session::put('report_end_date', $req->end_date);
        Session::put('report_keyword', $keyword);
        
        $records = $this->queryEarning($req->start_date, $req->end_date, $keyword);

        $records_count = $records->count();
        $records_ids = $records->pluck('sale_products.product_id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'product_name' => $record->model_name,
                'product_code' => $record->sku,
                'sales' => number_format($record->sum_amount - $record->sum_promo_amount, 2),
                'cost' => number_format($record->sum_cost, 2),
                'earning' => number_format($record->sum_amount - $record->sum_promo_amount - $record->sum_cost, 2),
            ];
        }

        return response()->json($data);
    }

    public function exportEarning() {
        $records = $this->queryEarning(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new EarningReportExport($records), 'earning-report.xlsx');
    }

    private function queryEarning(string $start_date, string $end_date, string | null $keyword) {
        $promo = DB::table('promotions')->select('id', 'amount');

        $sales = DB::table('sales')->where('type', Sale::TYPE_SO);
        
        $records = DB::table('sale_products')
            ->select(
                'sale_products.sale_id', 'sale_products.product_id AS product_id', 'products.model_name AS model_name', 'products.sku AS sku',
                DB::raw('SUM(sale_products.cost) as sum_cost'), 
                DB::raw('SUM(promo.amount) as sum_promo_amount'),
                DB::raw('SUM(sale_products.qty * sale_products.unit_price - sale_products.cost) as sum_amount'),
            )
            ->leftJoin('products', 'products.id', '=', 'sale_products.product_id')
            ->joinSub($sales, 'sales', function ($join) {
                $join->on('sales.id', '=', 'sale_products.sale_id');
            })
            ->leftJoinSub($promo, 'promo', function ($join) {
                $join->on('sale_products.promotion_id', '=', 'promo.id');
            });

        // Daterange
        if ($start_date != 'null' && $end_date != 'null') {
            if ($start_date == $end_date) {
                $records = $records->where('sales.created_at', 'like', '%' . $start_date . '%');
            } else {
                $end_date_next_day = Carbon::parse($end_date)->addDay()->format('Y-m-d');

                $records = $records->where('sales.created_at', '>=', $start_date)->where('sales.created_at', '<=', $end_date_next_day);
            }
        }
        // Search
        if ($keyword != null) {
            $records = $records->where(function ($q) use ($keyword) {
                return $q->where('products.model_name', 'like', '%' . $keyword . '%')
                    ->orWhere('products.sku', 'like', '%' . $keyword . '%');
            });
        }
        $records = $records->groupBy('sale_products.product_id')->orderBy('sale_products.id', 'desc');

        return $records;
    }

    private function clearSession() {
        Session::forget('report_start_date');
        Session::forget('report_end_date');
        Session::forget('report_keyword');
    }
}
