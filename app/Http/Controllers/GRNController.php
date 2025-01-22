<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CreditTerm;
use App\Models\GRN;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\ProductCost;
use App\Models\Scopes\BranchScope;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class GRNController extends Controller
{
    protected $grn;

    protected $prod;

    protected $prodChild;

    protected $prodCost;

    public function __construct()
    {
        $this->grn = new GRN;
        $this->prod = new Product;
        $this->prodChild = new ProductChild;
        $this->prodCost = new ProductCost;
    }

    public function index()
    {
        return view('grn.list');
    }

    public function getData(Request $req)
    {
        $records = $this->grn;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        $records = $records->groupBy('sku')->orderBy('id', 'desc');

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
            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('grn.form');
    }

    public function edit($sku)
    {
        $grns = $this->grn::where('sku', $sku)->get();

        if (count($grns) <= 0) {
            abort(404);
        }

        return view('grn.form', [
            'sku' => $sku,
            'grns' => $grns,
        ]);
    }

    public function upsert(Request $req)
    {
        // Validate form
        $rules = [
            'sku' => 'nullable',
            'our_po_no' => 'required',
            'term' => 'required',
            'our_po_date' => 'required',
            'supplier' => 'required',
            'supplier_do_no' => 'required',
            'company_group' => 'required',
            'product_id' => 'required',
            'product_id.*' => 'required',
            'qty' => 'required',
            'qty.*' => 'required',
            'uom' => 'required',
            'uom.*' => 'required',
            'unit_price' => 'required',
            'unit_price.*' => 'required',
            'total_price' => 'required',
            'total_price.*' => 'required',
        ];
        $req->validate($rules, [], [
            'product_id.*' => 'product',
            'qty.*' => 'quantity',
            'unit_price.*' => 'unit price',
        ]);

        try {
            DB::beginTransaction();

            if ($req->sku == null) {
                $existing_skus = GRN::withoutGlobalScope(BranchScope::class)->groupBy('sku')->pluck('sku')->toArray();
                $sku = generateSku('GR', $existing_skus, true);
            } else {
                $sku = $req->sku;

                $ids = $this->grn::where('sku', $sku)->pluck('id')->toArray();
                $this->grn::where('sku', $sku)->delete();
                Branch::where('object_type', GRN::class)->whereIn('object_id', $ids)->delete();
            }

            for ($i = 0; $i < count($req->product_id); $i++) {
                $grn = $this->grn::create([
                    'sku' => $sku,
                    'our_po_no' => $req->our_po_no,
                    'term' => $req->term,
                    'our_po_date' => $req->our_po_date,
                    'branch_id' => getCurrentUserBranch(),
                    'supplier_id' => $req->supplier,
                    'supplier_do_no' => $req->supplier_do_no,
                    'company_group' => $req->company_group,
                    'product_id' => $req->product_id[$i],
                    'qty' => $req->qty[$i],
                    'uom' => $req->uom[$i],
                    'unit_price' => $req->unit_price[$i],
                    'total_price' => $req->total_price[$i],
                ]);
                (new Branch)->assign(GRN::class, $grn->id);
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

    public function pdf($sku)
    {
        $grns = $this->grn::where('sku', $sku)->get();

        if (count($grns) <= 0) {
            abort(404);
        }

        $pdf = Pdf::loadView('grn.powercool_pdf', [
            'date' => now()->format('d/m/Y'),
            'sku' => $sku,
            'grns' => $grns,
            'supplier' => Supplier::where('id', $grns[0]->supplier_id)->first(),
            'our_po_no' => $grns[0]->our_po_no,
            'term' => CreditTerm::where('id', $grns[0]->term)->value('name'),
            'our_po_date' => Carbon::parse($grns[0]->our_po_date)->format('d/m/Y'),
            'store' => (new Branch)->keyToLabel($grns[0]->branch_id),
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->stream();
    }

    public function stockIn(Request $req)
    {
        try {
            DB::beginTransaction();

            $now = now();
            $data = [];
            for ($i = 0; $i < count($req->product); $i++) {
                if ($req->{'serial_no_'.$req->product[$i]} != null) {
                    $grn = $this->grn::where('sku', $req->sku)->where('product_id', $req->product[$i])->first();

                    $serial_no = explode(',', $req->{'serial_no_'.$req->product[$i]});

                    for ($j = 0; $j < count($serial_no); $j++) {
                        $data[] = [
                            'product_id' => $req->product[$i],
                            'sku' => $serial_no[$j],
                            'qty' => null,
                            'unit_price' => $grn->unit_price,
                            'total_price' => $grn->total_price,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $this->prodChild::create([
                            'product_id' => $req->product[$i],
                            'sku' => $serial_no[$j],
                            'location' => $this->prodChild::LOCATION_WAREHOUSE,
                        ]);
                    }
                } elseif ($req->{'qty_'.$req->product[$i]} != null && $req->{'qty_'.$req->product[$i]} != 0) {
                    $grn = $this->grn::where('sku', $req->sku)->where('product_id', $req->product[$i])->first();

                    $data[] = [
                        'product_id' => $req->product[$i],
                        'sku' => null,
                        'qty' => $req->{'qty_'.$req->product[$i]},
                        'unit_price' => $grn->unit_price,
                        'total_price' => $grn->total_price,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $this->prod::where('id', $req->product[$i])->increment('qty', $req->{'qty_'.$req->product[$i]});
                }
            }
            $this->prodCost::insert($data);

            DB::commit();

            return back()->with('success', 'Item stocked in');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function sync(Request $request)
    {
        $request->validate([
            'grns' => 'required|array',
            'grns.*.sku' => 'required',
        ]);

        $selectedInvoices = $request->input('grns');

        foreach ($selectedInvoices as $invoiceData) {
            $invoice = GRN::where('sku', $invoiceData['sku'])->first();
            if ($invoice) {
                $invoice->sync = 0;
                $invoice->save();
            }
        }

        return response()->json([
            'message' => 'GRNs updated successfully.',
        ]);
    }
}
