<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use App\Models\Warranty;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    protected $so;
    protected $sp;
    protected $spc;
    protected $product;

    public function __construct(Sale $sale, SaleProduct $sale_product, SaleProductChild $sale_product_child, Product $product) {
        $this->so = $sale;
        $this->sp = $sale_product;
        $this->spc = $sale_product_child;
        $this->product = $product;
    }

    public function index() {
        return view('warranty.list');
    }

    public function getData(Request $req) {
        $so_ids = $this->so::where('type', Sale::TYPE_SO)->pluck('id'); 

        $records = $this->sp::whereIn('sale_id', $so_ids);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
                $q->whereHas('sale', function($query) use ($keyword) {
                    $query->where('sku', 'like', '%'.$keyword.'%');
                })
                ->orWhereHas('product', function($query) use ($keyword) {
                    $query->where('sku', 'like', '%'.$keyword.'%');
                });
            });
        }
        // Order
        $records = $records->orderBy('id', 'desc');

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
                'sale_order_id' => $record->sale->sku,
                'product' => $record->product,
                'warranty' => $record->warrantyPeriod == null ? null : $record->warrantyPeriod->name,
            ];
        }
                
        return response()->json($data);
    }
}
