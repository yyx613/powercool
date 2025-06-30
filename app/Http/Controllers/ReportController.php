<?php

namespace App\Http\Controllers;

use App\Exports\EarningReportExport;
use App\Exports\ProductionReportExport;
use App\Exports\SalesReportExport;
use App\Exports\ServiceReportExport;
use App\Exports\StockReportExport;
use App\Exports\TechnicianStockReportExport;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\Production;
use App\Models\ProductionMilestoneMaterial;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\TaskMilestoneInventory;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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
    protected $ms;
    protected $taskMs;
    protected $taskMsInventory;

    public function __construct() {
        $this->production = new Production;
        $this->product = new Product;
        $this->sale = new Sale;
        $this->saleProduct = new SaleProduct;
        $this->productionMsMaterial = new ProductionMilestoneMaterial();
        $this->ms = new Milestone();
        $this->taskMs = new TaskMilestone();
        $this->taskMsInventory = new TaskMilestoneInventory();
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

    public function exportInExcelProduction() {
        $records = $this->queryProduction(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new ProductionReportExport($records), 'production-report.xlsx');
    }

    public function exportInPdfProduction() {
        $records = $this->queryProduction(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        $pdf = Pdf::loadView('report.production_list_pdf', [
            'records' => $records
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->download('production-report.pdf');
    }

    private function queryProduction(?string $start_date='null', ?string $end_date='null', ?string $keyword) {
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

    public function exportInExcelSales() {
        $records = $this->querySales(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new SalesReportExport($records), 'sales-report.xlsx');
    }

    public function exportInPdfSales() {
        $records = $this->querySales(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        $pdf = Pdf::loadView('report.sales_list_pdf', [
            'records' => $records
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->download('sales-report.pdf');
    }

    private function querySales(?string $start_date='null', ?string $end_date='null', ?string $keyword) {
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
                'sales.id AS id', 'sales_agents.name AS saleperson', DB::raw('SUM(sum_qty) as sum_qty'),
                DB::raw('SUM(sum_amount) as sum_amount'), DB::raw('SUM(sum_promo_amount) as sum_promo_amount'),
                'sales.payment_amount AS payment_amount', 'sales.created_at'
            )
            ->where('sales.type', Sale::TYPE_SO)
            ->join('sales_agents', 'sales_agents.id', '=', 'sales.sale_id')
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
            $data['data'][] = [
                'id' => $record->id,
                'product_name' => $record->model_name,
                'product_code' => $record->sku,
                'warehouse_available_stock' => $record->warehouseAvailableStock(),
                'warehouse_reserved_stock' => $record->warehouseReservedStock(),
                'warehouse_on_hold_stock' => $record->warehouseOnHoldStock(),
            ];
        }

        return response()->json($data);
    }

    public function exportInExcelStock() {
        $records = $this->queryStock(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new StockReportExport($records), 'stock-report.xlsx');
    }

    public function exportInPdfStock() {
        $records = $this->queryStock(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        $pdf = Pdf::loadView('report.stock_list_pdf', [
            'records' => $records
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->download('stock-report.pdf');
    }

    private function queryStock(?string $start_date='null', ?string $end_date='null', ?string $keyword) {
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

    public function exportInExcelEarning() {
        $records = $this->queryEarning(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new EarningReportExport($records), 'earning-report.xlsx');
    }

    public function exportInPdfEarning() {
        $records = $this->queryEarning(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        $pdf = Pdf::loadView('report.earning_list_pdf', [
            'records' => $records
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->download('earning-report.pdf');
    }

    private function queryEarning(?string $start_date='null', ?string $end_date='null', ?string $keyword) {
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

    public function indexService() {
        return view('report.service_list');
    }

    public function getDataService(Request $req) {
        $keyword = null;
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];
        }
        Session::put('report_start_date', $req->start_date);
        Session::put('report_end_date', $req->end_date);
        Session::put('report_keyword', $keyword);
        
        $records = $this->queryService($req->start_date, $req->end_date, $keyword);

        $records_count = $records->count();
        $records_ids = $records->pluck('tasks.id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'product' => $record->product,
                'service_count' => $record->service_count,
                'income_generated' => number_format($record->income_generated, 2),
            ];
        }

        return response()->json($data);
    }

    public function exportInExcelService() {
        $records = $this->queryService(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new ServiceReportExport($records), 'service-report.xlsx');
    }

    public function exportInPdfService() {
        $records = $this->queryService(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        $pdf = Pdf::loadView('report.service_list_pdf', [
            'records' => $records
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->download('service-report.pdf');
    }

    private function queryService(?string $start_date='null', ?string $end_date='null', ?string $keyword) {
        $part_replacement_ms_id = $this->ms::where('type', $this->ms::TYPE_SERVICE_TASK)
            ->where('is_custom', false)
            ->where('name', 'Part Replacement')
            ->value('id');

        $records = DB::table('tasks')
            ->select(
                'tasks.id AS id', 'products.model_name AS product',
                DB::raw('SUM(tasks.amount_to_collect) as income_generated'), 
                DB::raw('COUNT(*) as service_count'), 
            )
            ->where('tasks.type', Task::TYPE_TECHNICIAN)
            ->leftJoin('task_milestone', 'tasks.id', '=', 'task_milestone.task_id')
            ->whereNotNull('task_milestone.submitted_at')
            ->where('task_milestone.milestone_id', $part_replacement_ms_id)
            ->leftJoin('products', 'products.id', '=', 'tasks.product_id')
            ->groupBy('tasks.product_id');

        // Daterange
        if ($start_date != 'null' && $end_date != 'null') {
            if ($start_date == $end_date) {
                $records = $records->where('tasks.created_at', 'like', '%' . $start_date . '%');
            } else {
                $end_date_next_day = Carbon::parse($end_date)->addDay()->format('Y-m-d');

                $records = $records->where('tasks.created_at', '>=', $start_date)->where('tasks.created_at', '<=', $end_date_next_day);
            }
        }
        // Search
        if ($keyword != null) {
            $records = $records->where(function ($q) use ($keyword) {
                return $q->Where('products.model_name', 'like', '%' . $keyword . '%');
            });
        }
        $records = $records;

        return $records;
    }

    public function indexTechnicianStock() {
        return view('report.technician_stock_list');
    }

    public function getDataTechnicianStock(Request $req) {
        $keyword = null;
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];
        }
        Session::put('report_start_date', $req->start_date);
        Session::put('report_end_date', $req->end_date);
        Session::put('report_keyword', $keyword);
        
        $records = $this->queryTechnicianStock($req->start_date, $req->end_date, $keyword);

        $records_count = $records->count();
        $records_ids = $records->pluck('tasks.id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'technician' => $record->technician,
                'task_sku' => $record->sku,
                'product_for_replacement' => $record->product_for_replacement,
                'material_used_qty' => $record->material_used_qty,
            ];
        }

        return response()->json($data);
    }

    public function exportInExcelTechnicianStock() {
        $records = $this->queryTechnicianStock(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new TechnicianStockReportExport($records), 'technician-stock-report.xlsx');
    }

    public function exportInPdfTechnicianStock() {
        $records = $this->queryTechnicianStock(Session::get('report_start_date'), Session::get('report_end_date' ), Session::get('report_keyword'));
        $records = $records->get();

        $pdf = Pdf::loadView('report.technician_stock_list_pdf', [
            'records' => $records
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->download('technician-stock-report.pdf');
    }

    private function queryTechnicianStock(?string $start_date='null', ?string $end_date='null', ?string $keyword) {
        $part_replacement_ms_id = $this->ms::where('type', $this->ms::TYPE_SERVICE_TASK)
            ->where('is_custom', false)
            ->where('name', 'Part Replacement')
            ->value('id');

        $records = DB::table('tasks')
            ->select(
                'tasks.id AS id', 'tasks.sku AS sku', 'users.name AS technician', 'products.model_name AS product_for_replacement',
                DB::raw('SUM(task_milestone_inventories.qty) as material_used_qty'), 
            )
            ->where('tasks.type', Task::TYPE_TECHNICIAN)
            ->leftJoin('task_milestone', 'tasks.id', '=', 'task_milestone.task_id')
            ->whereNotNull('task_milestone.submitted_at')
            ->where('task_milestone.milestone_id', $part_replacement_ms_id)
            ->leftJoin('user_task', 'tasks.id', '=', 'user_task.task_id')
            ->leftJoin('users', 'user_task.id', '=', 'users.id')
            ->leftJoin('products', 'products.id', '=', 'tasks.product_id')
            ->leftJoin('task_milestone_inventories', 'task_milestone_inventories.task_milestone_id', '=', 'task_milestone.id');

        // Daterange
        if ($start_date != 'null' && $end_date != 'null') {
            if ($start_date == $end_date) {
                $records = $records->where('tasks.created_at', 'like', '%' . $start_date . '%');
            } else {
                $end_date_next_day = Carbon::parse($end_date)->addDay()->format('Y-m-d');

                $records = $records->where('tasks.created_at', '>=', $start_date)->where('tasks.created_at', '<=', $end_date_next_day);
            }
        }
        // Search
        if ($keyword != null) {
            $records = $records->where(function ($q) use ($keyword) {
                return $q->where('users.name', 'like', '%' . $keyword . '%')
                    ->orWhere('tasks.sku', 'like', '%' . $keyword . '%')
                    ->orWhere('products.model_name', 'like', '%' . $keyword . '%');
            });
        }
        $records = $records;

        return $records;
    }

    private function clearSession() {
        Session::forget('report_start_date');
        Session::forget('report_end_date');
        Session::forget('report_keyword');
    }
}
