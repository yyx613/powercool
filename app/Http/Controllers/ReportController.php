<?php

namespace App\Http\Controllers;

use App\Exports\EarningReportExport;
use App\Exports\ProductionReportExport;
use App\Exports\SalesReportExport;
use App\Exports\ServiceReportExport;
use App\Exports\StockCardReportExport;
use App\Exports\StockReportExport;
use App\Exports\TechnicianStockReportExport;
use App\Services\StockCardService;
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

    public function __construct()
    {
        $this->production = new Production;
        $this->product = new Product;
        $this->sale = new Sale;
        $this->saleProduct = new SaleProduct;
        $this->productionMsMaterial = new ProductionMilestoneMaterial();
        $this->ms = new Milestone();
        $this->taskMs = new TaskMilestone();
        $this->taskMsInventory = new TaskMilestoneInventory();
    }

    public function indexProduction()
    {
        $this->clearSession();
        return view('report.production_list');
    }

    public function getDataProduction(Request $req)
    {
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
                'product_name' => $record->product->model_desc,
                'product_code' => $record->product->sku,
            ];
        }

        return response()->json($data);
    }

    public function exportInExcelProduction()
    {
        $records = $this->queryProduction(Session::get('report_start_date'), Session::get('report_end_date'), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new ProductionReportExport($records), 'production-report.xlsx');
    }

    public function exportInPdfProduction()
    {
        $records = $this->queryProduction(Session::get('report_start_date'), Session::get('report_end_date'), Session::get('report_keyword'));
        $records = $records->get();

        $pdf = Pdf::loadView('report.production_list_pdf', [
            'records' => $records
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->download('production-report.pdf');
    }

    private function queryProduction(?string $start_date = 'null', ?string $end_date = 'null', ?string $keyword)
    {
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
                    return $q->where('model_desc', 'like', '%' . $keyword . '%')->orWhere('sku', 'like', '%' . $keyword . '%');
                });
            });
        }
        $records = $records->orderBy('id', 'desc');

        return $records;
    }

    public function indexSales()
    {
        $this->clearSession();
        return view('report.sales_list');
    }

    public function getDataSales(Request $req)
    {
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
            $overall_amount += $record->sum_amount - $record->sum_promo_amount - ($record->paymentAmount ?? 0);

            $data['data'][] = [
                'id' => $record->id,
                'salesperson' => $record->saleperson,
                'qty' => $record->sum_qty,
                'promo' => number_format($record->sum_promo_amount, 2),
                'amount' => number_format($record->sum_amount - $record->sum_promo_amount, 2),
                'outstanding_amount' => number_format($record->sum_amount - $record->sum_promo_amount - ($record->paymentAmount ?? 0), 2),
                'overall_qty' => $overall_qty,
                'overall_amount' => number_format($overall_amount, 2),
            ];
        }

        return response()->json($data);
    }

    public function exportInExcelSales()
    {
        $records = $this->querySales(Session::get('report_start_date'), Session::get('report_end_date'), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new SalesReportExport($records), 'sales-report.xlsx');
    }

    public function exportInPdfSales()
    {
        $records = $this->querySales(Session::get('report_start_date'), Session::get('report_end_date'), Session::get('report_keyword'));
        $records = $records->get();

        $pdf = Pdf::loadView('report.sales_list_pdf', [
            'records' => $records
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->download('sales-report.pdf');
    }

    private function querySales(?string $start_date = 'null', ?string $end_date = 'null', ?string $keyword)
    {
        $promo = DB::table('promotions')
            ->select('id', 'amount');

        $amount = DB::table('sale_products')
            ->select(
                'sale_products.sale_id',
                'sale_products.promotion_id',
                DB::raw('SUM(sale_products.qty) as sum_qty'),
                DB::raw('SUM(sale_products.qty * sale_products.unit_price) as sum_amount'),
                DB::raw('SUM(promo.amount) as sum_promo_amount')
            )
            ->groupBy('sale_products.sale_id')
            ->leftJoinSub($promo, 'promo', function ($join) {
                $join->on('sale_products.promotion_id', '=', 'promo.id');
            });

        $payment_amount = DB::table('sale_payment_amounts')
            ->select(
                'sale_payment_amounts.sale_id',
                DB::raw('SUM(sale_payment_amounts.amount) as paymentAmount')
            )
            ->groupBy('sale_payment_amounts.sale_id');

        $records = DB::table('sales')
            ->select(
                'sales.id AS id',
                'sales_agents.name AS saleperson',
                DB::raw('SUM(sum_qty) as sum_qty'),
                DB::raw('SUM(sum_amount) as sum_amount'),
                DB::raw('SUM(sum_promo_amount) as sum_promo_amount'),
                'payment_amount.paymentAmount AS payment_amount',
                'sales.created_at'
            )
            ->where('sales.type', Sale::TYPE_SO)
            ->join('sales_agents', 'sales_agents.id', '=', 'sales.sale_id')
            ->joinSub($amount, 'amount', function ($join) {
                $join->on('sales.id', '=', 'amount.sale_id');
            })
            ->joinSub($payment_amount, 'payment_amount', function ($join) {
                $join->on('sales.id', '=', 'payment_amount.sale_id');
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

    public function indexStock()
    {
        $this->clearSession();

        return view('report.stock_list');
    }

    public function getDataStock(Request $req)
    {
        $keyword = null;
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];
        }

        $start = $req->start_date != null && $req->start_date !== 'null' ? $req->start_date : null;
        $end = $req->end_date != null && $req->end_date !== 'null' ? $req->end_date : null;
        $companyGroup = $req->company_group != null && $req->company_group !== 'null' && $req->company_group !== ''
            ? (int) $req->company_group
            : null;
        $brand = $req->brand != null && $req->brand !== 'null' && $req->brand !== ''
            ? (int) $req->brand
            : null;

        Session::put('report_start_date', $start);
        Session::put('report_end_date', $end);
        Session::put('report_keyword', $keyword);
        Session::put('report_company_group', $companyGroup);
        Session::put('report_brand', $brand);

        $items = (new StockCardService)->getMovements($start, $end, $keyword, $companyGroup, $brand, Product::TYPE_RAW_MATERIAL);

        $total = count($items);
        $page = max(1, (int) $req->input('page', 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $slice = array_slice($items, $offset, $perPage);

        $data = [
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => [],
        ];

        foreach ($slice as $item) {
            $product = $item['product'];
            $location = $item['locations'][0] ?? null;
            $inQty = 0;
            $outQty = 0;
            $inCost = 0.0;
            $outCost = 0.0;
            foreach (($location['movements'] ?? []) as $mv) {
                if ($mv['in_out_qty'] >= 0) {
                    $inQty += $mv['in_out_qty'];
                } else {
                    $outQty += abs($mv['in_out_qty']);
                }
                if (($mv['total_cost'] ?? 0) >= 0) {
                    $inCost += $mv['total_cost'] ?? 0;
                } else {
                    $outCost += abs($mv['total_cost'] ?? 0);
                }
            }

            $data['data'][] = [
                'product_name' => $product->model_desc,
                'product_code' => $product->sku,
                'company' => $item['company_label'] ?? 'Unassigned',
                'brand' => $item['brand_label'] ?? 'Unassigned',
                'location' => $location['location_label'] ?? '-',
                'bf_qty' => $location['bf_qty'] ?? 0,
                'in_qty' => $inQty,
                'out_qty' => $outQty,
                'closing_qty' => $location['closing_qty'] ?? 0,
                'bf_cost' => number_format($location['bf_cost'] ?? 0, 2),
                'in_cost' => number_format($inCost, 2),
                'out_cost' => number_format($outCost, 2),
                'closing_cost' => number_format($location['closing_cost'] ?? 0, 2),
            ];
        }

        return response()->json($data);
    }

    public function exportInPdfStock()
    {
        $companyGroup = Session::get('report_company_group');
        $brand = Session::get('report_brand');
        $items = (new StockCardService)->getMovements(
            Session::get('report_start_date'),
            Session::get('report_end_date'),
            Session::get('report_keyword'),
            $companyGroup,
            $brand,
            Product::TYPE_RAW_MATERIAL,
        );

        $pdf = Pdf::loadView('report.stock_list_pdf', [
            'items' => $items,
            'start_date' => Session::get('report_start_date'),
            'end_date' => Session::get('report_end_date'),
            'company_group_label' => $companyGroup ? StockCardService::companyLabelFor($companyGroup) : 'All',
            'company_header' => StockCardService::companyHeaderFor($companyGroup),
            'brand_label' => $brand ? StockCardService::brandLabelFor($brand) : 'All',
            'brand_header' => $brand ? StockCardService::brandLabelFor($brand) : null,
        ]);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('stock-report.pdf');
    }

    public function exportInExcelStock()
    {
        $companyGroup = Session::get('report_company_group');
        $brand = Session::get('report_brand');
        $items = (new StockCardService)->getMovements(
            Session::get('report_start_date'),
            Session::get('report_end_date'),
            Session::get('report_keyword'),
            $companyGroup,
            $brand,
            Product::TYPE_RAW_MATERIAL,
        );

        return Excel::download(
            new StockReportExport(
                $items,
                StockCardService::companyHeaderFor($companyGroup),
                $brand ? StockCardService::brandLabelFor($brand) : null,
                Session::get('report_start_date'),
                Session::get('report_end_date'),
                $companyGroup ? StockCardService::companyLabelFor($companyGroup) : 'All',
                $brand ? StockCardService::brandLabelFor($brand) : 'All',
                optional(auth()->user())->name ?? '',
            ),
            'stock-report.xlsx'
        );
    }

    public function indexEarning()
    {
        return view('report.earning_list');
    }

    public function getDataEarning(Request $req)
    {
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
                'product_name' => $record->model_desc,
                'product_code' => $record->sku,
                'sales' => number_format($record->sum_amount - $record->sum_promo_amount, 2),
                'cost' => number_format($record->sum_cost, 2),
                'earning' => number_format($record->sum_amount - $record->sum_promo_amount - $record->sum_cost, 2),
            ];
        }

        return response()->json($data);
    }

    public function exportInExcelEarning()
    {
        $records = $this->queryEarning(Session::get('report_start_date'), Session::get('report_end_date'), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new EarningReportExport($records), 'earning-report.xlsx');
    }

    public function exportInPdfEarning()
    {
        $records = $this->queryEarning(Session::get('report_start_date'), Session::get('report_end_date'), Session::get('report_keyword'));
        $records = $records->get();

        $pdf = Pdf::loadView('report.earning_list_pdf', [
            'records' => $records
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->download('earning-report.pdf');
    }

    private function queryEarning(?string $start_date = 'null', ?string $end_date = 'null', ?string $keyword)
    {
        $promo = DB::table('promotions')->select('id', 'amount');

        $sales = DB::table('sales')->where('type', Sale::TYPE_SO);

        $records = DB::table('sale_products')
            ->select(
                'sale_products.sale_id',
                'sale_products.product_id AS product_id',
                'products.model_desc AS model_desc',
                'products.sku AS sku',
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
                return $q->where('products.model_desc', 'like', '%' . $keyword . '%')
                    ->orWhere('products.sku', 'like', '%' . $keyword . '%');
            });
        }
        $records = $records->groupBy('sale_products.product_id')->orderBy('sale_products.id', 'desc');

        return $records;
    }

    public function indexService()
    {
        return view('report.service_list');
    }

    public function getDataService(Request $req)
    {
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

    public function exportInExcelService()
    {
        $records = $this->queryService(Session::get('report_start_date'), Session::get('report_end_date'), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new ServiceReportExport($records), 'service-report.xlsx');
    }

    public function exportInPdfService()
    {
        $records = $this->queryService(Session::get('report_start_date'), Session::get('report_end_date'), Session::get('report_keyword'));
        $records = $records->get();

        $pdf = Pdf::loadView('report.service_list_pdf', [
            'records' => $records
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->download('service-report.pdf');
    }

    private function queryService(?string $start_date = 'null', ?string $end_date = 'null', ?string $keyword)
    {
        $part_replacement_ms_id = $this->ms::where('type', $this->ms::TYPE_SERVICE_TASK)
            ->where('is_custom', false)
            ->where('name', 'Part Replacement')
            ->value('id');

        $records = DB::table('tasks')
            ->select(
                'tasks.id AS id',
                'products.model_desc AS product',
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
                return $q->Where('products.model_desc', 'like', '%' . $keyword . '%');
            });
        }
        $records = $records;

        return $records;
    }

    public function indexTechnicianStock()
    {
        return view('report.technician_stock_list');
    }

    public function getDataTechnicianStock(Request $req)
    {
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

    public function exportInExcelTechnicianStock()
    {
        $records = $this->queryTechnicianStock(Session::get('report_start_date'), Session::get('report_end_date'), Session::get('report_keyword'));
        $records = $records->get();

        return Excel::download(new TechnicianStockReportExport($records), 'technician-stock-report.xlsx');
    }

    public function exportInPdfTechnicianStock()
    {
        $records = $this->queryTechnicianStock(Session::get('report_start_date'), Session::get('report_end_date'), Session::get('report_keyword'));
        $records = $records->get();

        $pdf = Pdf::loadView('report.technician_stock_list_pdf', [
            'records' => $records
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->download('technician-stock-report.pdf');
    }

    private function queryTechnicianStock(?string $start_date = 'null', ?string $end_date = 'null', ?string $keyword)
    {
        $part_replacement_ms_id = $this->ms::where('type', $this->ms::TYPE_SERVICE_TASK)
            ->where('is_custom', false)
            ->where('name', 'Part Replacement')
            ->value('id');

        $records = DB::table('tasks')
            ->select(
                'tasks.id AS id',
                'tasks.sku AS sku',
                'users.name AS technician',
                'products.model_desc AS product_for_replacement',
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
                    ->orWhere('products.model_desc', 'like', '%' . $keyword . '%');
            });
        }
        $records = $records;

        return $records;
    }

    private function clearSession()
    {
        Session::forget('report_start_date');
        Session::forget('report_end_date');
        Session::forget('report_keyword');
        Session::forget('report_company_group');
        Session::forget('report_brand');
    }

    public function indexStockCard()
    {
        $this->clearSession();

        return view('report.stock_card_list');
    }

    public function getDataStockCard(Request $req)
    {
        $keyword = null;
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];
        }

        $start = $req->start_date != null && $req->start_date !== 'null' ? $req->start_date : null;
        $end = $req->end_date != null && $req->end_date !== 'null' ? $req->end_date : null;
        $companyGroup = $req->company_group != null && $req->company_group !== 'null' && $req->company_group !== ''
            ? (int) $req->company_group
            : null;
        $brand = $req->brand != null && $req->brand !== 'null' && $req->brand !== ''
            ? (int) $req->brand
            : null;

        Session::put('report_start_date', $start);
        Session::put('report_end_date', $end);
        Session::put('report_keyword', $keyword);
        Session::put('report_company_group', $companyGroup);
        Session::put('report_brand', $brand);

        $items = (new StockCardService)->getMovements($start, $end, $keyword, $companyGroup, $brand, Product::TYPE_PRODUCT);

        $total = count($items);
        $page = max(1, (int) $req->input('page', 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $slice = array_slice($items, $offset, $perPage);

        $data = [
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => [],
        ];

        foreach ($slice as $item) {
            $product = $item['product'];
            $location = $item['locations'][0] ?? null;
            $inQty = 0;
            $outQty = 0;
            $inCost = 0.0;
            $outCost = 0.0;
            foreach (($location['movements'] ?? []) as $mv) {
                if ($mv['in_out_qty'] >= 0) {
                    $inQty += $mv['in_out_qty'];
                } else {
                    $outQty += abs($mv['in_out_qty']);
                }
                if (($mv['total_cost'] ?? 0) >= 0) {
                    $inCost += $mv['total_cost'] ?? 0;
                } else {
                    $outCost += abs($mv['total_cost'] ?? 0);
                }
            }

            $data['data'][] = [
                'product_name' => $product->model_desc,
                'product_code' => $product->sku,
                'company' => $item['company_label'] ?? 'Unassigned',
                'brand' => $item['brand_label'] ?? 'Unassigned',
                'location' => $location['location_label'] ?? '-',
                'bf_qty' => $location['bf_qty'] ?? 0,
                'in_qty' => $inQty,
                'out_qty' => $outQty,
                'closing_qty' => $location['closing_qty'] ?? 0,
                'bf_cost' => number_format($location['bf_cost'] ?? 0, 2),
                'in_cost' => number_format($inCost, 2),
                'out_cost' => number_format($outCost, 2),
                'closing_cost' => number_format($location['closing_cost'] ?? 0, 2),
            ];
        }

        return response()->json($data);
    }

    public function exportInPdfStockCard()
    {
        $companyGroup = Session::get('report_company_group');
        $brand = Session::get('report_brand');
        $items = (new StockCardService)->getMovements(
            Session::get('report_start_date'),
            Session::get('report_end_date'),
            Session::get('report_keyword'),
            $companyGroup,
            $brand,
            Product::TYPE_PRODUCT,
        );

        $pdf = Pdf::loadView('report.stock_card_list_pdf', [
            'items' => $items,
            'start_date' => Session::get('report_start_date'),
            'end_date' => Session::get('report_end_date'),
            'company_group_label' => $companyGroup ? StockCardService::companyLabelFor($companyGroup) : 'All',
            'company_header' => StockCardService::companyHeaderFor($companyGroup),
            'brand_label' => $brand ? StockCardService::brandLabelFor($brand) : 'All',
            'brand_header' => $brand ? StockCardService::brandLabelFor($brand) : null,
        ]);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('stock-card-report.pdf');
    }

    public function exportInExcelStockCard()
    {
        $companyGroup = Session::get('report_company_group');
        $brand = Session::get('report_brand');
        $items = (new StockCardService)->getMovements(
            Session::get('report_start_date'),
            Session::get('report_end_date'),
            Session::get('report_keyword'),
            $companyGroup,
            $brand,
            Product::TYPE_PRODUCT,
        );

        return Excel::download(
            new StockCardReportExport(
                $items,
                StockCardService::companyHeaderFor($companyGroup),
                $brand ? StockCardService::brandLabelFor($brand) : null,
                Session::get('report_start_date'),
                Session::get('report_end_date'),
                $companyGroup ? StockCardService::companyLabelFor($companyGroup) : 'All',
                $brand ? StockCardService::brandLabelFor($brand) : 'All',
                optional(auth()->user())->name ?? '',
            ),
            'stock-card-report.xlsx'
        );
    }
}
