<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Dealer;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\ProductChild;
use App\Models\Role;
use App\Models\ServiceForm;
use App\Models\ServiceFormProduct;
use App\Models\ServiceFormProductWarrantyPeriod;
use App\Models\Setting;
use App\Models\UOM;
use App\Models\User;
use App\Models\WarrantyPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ServiceFormController extends Controller
{
    protected $serviceForm;

    public function __construct(ServiceForm $serviceForm)
    {
        $this->serviceForm = $serviceForm;
    }

    public function index()
    {
        $page = Session::get('service-form-page');

        return view('service_form.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        Session::put('service-form-page', $req->page);

        $records = $this->serviceForm->with(['customer', 'technician']);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%'.$keyword.'%')
                    ->orWhere('model_no', 'like', '%'.$keyword.'%')
                    ->orWhere('serial_no', 'like', '%'.$keyword.'%')
                    ->orWhereHas('customer', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%'.$keyword.'%')
                            ->orWhere('company_name', 'like', '%'.$keyword.'%');
                    });
            });
        }

        // Order
        $records = $records->orderBy('id', 'desc');

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
                'id' => Crypt::encrypt($record->id),
                'sku' => $record->sku,
                'date' => $record->date ? $record->date->format('d M Y') : '-',
                'customer_name' => $record->customer ? ($record->customer->company_name ?? $record->customer->name) : '-',
                'technician' => $record->technician ? $record->technician->name : '-',
                'created_at' => $record->created_at->format('d M Y'),
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        $technicians = User::whereHas('roles', function ($q) {
            $q->where('id', Role::TECHNICIAN);
        })->orderBy('name')->get();

        $sst = Setting::where('key', Setting::SST_KEY)->value('value') ?? 0;
        $paymentMethods = PaymentMethod::orderBy('name')->get();
        $uoms = UOM::orderBy('name')->get();

        return view('service_form.form', [
            'sku' => $this->serviceForm->generateSku(),
            'technicians' => $technicians,
            'warranty_statuses' => ServiceForm::getWarrantyStatuses(),
            'warranty_periods' => WarrantyPeriod::orderBy('name')->get(),
            'checklist_items' => ServiceForm::CHECKLIST_ITEMS,
            'sst' => $sst,
            'payment_methods' => $paymentMethods,
            'uoms' => $uoms,
        ]);
    }

    public function edit($serviceForm)
    {
        $serviceForm = Crypt::decrypt($serviceForm);
        $serviceForm = $this->serviceForm::with([
            'customer',
            'customerLocation',
            'product',
            'invoice',
            'dealer',
            'technician',
            'products.product.sellingPrices',
            'products.warrantyPeriods',
            'paymentMethod',
        ])->findOrFail($serviceForm);

        $technicians = User::whereHas('roles', function ($q) {
            $q->where('id', Role::TECHNICIAN);
        })->orderBy('name')->get();

        $sst = Setting::where('key', Setting::SST_KEY)->value('value') ?? 0;
        $paymentMethods = PaymentMethod::orderBy('name')->get();
        $uoms = UOM::orderBy('name')->get();

        return view('service_form.form', [
            'service_form' => $serviceForm,
            'technicians' => $technicians,
            'warranty_statuses' => ServiceForm::getWarrantyStatuses(),
            'warranty_periods' => WarrantyPeriod::orderBy('name')->get(),
            'checklist_items' => ServiceForm::CHECKLIST_ITEMS,
            'sst' => $sst,
            'payment_methods' => $paymentMethods,
            'uoms' => $uoms,
        ]);
    }

    public function upsert(Request $req, $serviceForm = null)
    {
        $rules = [
            'date' => 'nullable|date',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_location_id' => 'nullable|exists:customer_locations,id',
            'contact_person' => 'nullable|string|max:255',
            'contact_no' => 'nullable|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'model_no' => 'nullable|string|max:255',
            'serial_no' => 'nullable|string|max:255',
            'invoice_id' => 'nullable|exists:invoices,id',
            'invoice_no' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
            'warranty_status' => 'nullable|in:1,2',
            'warranty_period_id' => 'nullable|exists:warranty_periods,id',
            'dealer_id' => 'nullable|exists:dealers,id',
            'dealer_name' => 'nullable|string|max:255',
            'dealer_contact_no' => 'nullable|string|max:255',
            'nature_of_problem' => 'nullable|string',
            'date_to_attend' => 'nullable|date',
            'technician_id' => 'nullable|exists:users,id',
            'validity' => 'nullable|string|max:255',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'quotation_remark' => 'nullable|string',
            // Line items validation (individual arrays)
            'line_product_id' => 'nullable|array',
            'line_product_id.*' => 'nullable|exists:products,id',
            'line_custom_desc' => 'nullable|array',
            'line_custom_desc.*' => 'nullable|string|max:255',
            'line_qty' => 'nullable|array',
            'line_qty.*' => 'nullable|integer|min:1',
            'line_unit_price' => 'nullable|array',
            'line_unit_price.*' => 'nullable|numeric|min:0',
            'line_discount' => 'nullable|array',
            'line_discount.*' => 'nullable|numeric|min:0',
            'line_uom' => 'nullable|array',
            'line_uom.*' => 'nullable|string|max:50',
            'line_remark' => 'nullable|array',
            'line_remark.*' => 'nullable|string',
            'line_is_foc' => 'nullable|array',
            'line_is_foc.*' => 'nullable|in:0,1',
            'line_with_sst' => 'nullable|array',
            'line_with_sst.*' => 'nullable|in:0,1',
            'line_warranty_period' => 'nullable|array',
            'line_warranty_period.*' => 'nullable|array',
            'line_warranty_period.*.*' => 'nullable|exists:warranty_periods,id',
        ];

        $req->validate($rules);

        try {
            DB::beginTransaction();

            // Build report checklist from form input
            $reportChecklist = [];
            foreach (ServiceForm::CHECKLIST_ITEMS as $key => $label) {
                $checked = $req->has('checklist_'.$key);
                $remark = $req->input('checklist_'.$key.'_remark');

                // Only include if checked or has remark
                if ($checked || ! empty($remark)) {
                    $reportChecklist[$key] = [
                        'checked' => $checked,
                        'remark' => $remark ?? '',
                    ];
                }
            }

            // Get SST value
            $sstValue = Setting::where('key', Setting::SST_KEY)->value('value') ?? 0;

            $data = [
                'date' => $req->date,
                'customer_id' => $req->customer_id,
                'customer_location_id' => $req->customer_location_id,
                'contact_person' => $req->contact_person,
                'contact_no' => $req->contact_no,
                'product_id' => $req->product_id,
                'model_no' => $req->model_no,
                'serial_no' => $req->serial_no,
                'invoice_id' => $req->invoice_id,
                'invoice_no' => $req->invoice_no,
                'invoice_date' => $req->invoice_date,
                'warranty_status' => $req->warranty_status,
                'warranty_period_id' => $req->warranty_period_id,
                'dealer_id' => $req->dealer_id,
                'dealer_name' => $req->dealer_name,
                'dealer_contact_no' => $req->dealer_contact_no,
                'nature_of_problem' => $req->nature_of_problem,
                'date_to_attend' => $req->date_to_attend,
                'technician_id' => $req->technician_id,
                'report_checklist' => ! empty($reportChecklist) ? $reportChecklist : null,
                'validity' => $req->validity,
                'payment_method_id' => $req->payment_method_id,
                'quotation_remark' => $req->quotation_remark,
            ];

            if ($serviceForm != null) {
                $serviceForm = Crypt::decrypt($serviceForm);
                $sf = $this->serviceForm::findOrFail($serviceForm);

                $sf->update($data);

                // Delete existing products
                ServiceFormProduct::where('service_form_id', $sf->id)->delete();

                $message = 'Service Form updated';
            } else {
                $data['sku'] = $this->serviceForm->generateSku();
                $sf = $this->serviceForm::create($data);

                (new Branch)->assign(ServiceForm::class, $sf->id);

                $message = 'Service Form created';
            }

            // Create product line items from individual arrays
            $subtotal = 0;
            $totalTax = 0;

            $lineProductIds = $req->input('line_product_id', []);
            $lineCustomDescs = $req->input('line_custom_desc', []);
            $lineQtys = $req->input('line_qty', []);
            $lineUnitPrices = $req->input('line_unit_price', []);
            $lineDiscounts = $req->input('line_discount', []);
            $lineUoms = $req->input('line_uom', []);
            $lineRemarks = $req->input('line_remark', []);
            $lineFocs = $req->input('line_is_foc', []);
            $lineSsts = $req->input('line_with_sst', []);
            $lineWarrantyPeriods = $req->input('line_warranty_period', []);

            if (! empty($lineProductIds) || ! empty($lineCustomDescs)) {
                $itemCount = max(count($lineProductIds), count($lineCustomDescs));

                for ($index = 0; $index < $itemCount; $index++) {
                    $productId = $lineProductIds[$index] ?? null;
                    $customDesc = $lineCustomDescs[$index] ?? null;

                    // Skip empty items
                    if (empty($productId) && empty($customDesc)) {
                        continue;
                    }

                    $qty = $lineQtys[$index] ?? 1;
                    $unitPrice = $lineUnitPrices[$index] ?? 0;
                    $discount = $lineDiscounts[$index] ?? 0;
                    $uom = $lineUoms[$index] ?? null;
                    $remark = $lineRemarks[$index] ?? null;
                    $isFoc = isset($lineFocs[$index]) && $lineFocs[$index] == '1';
                    $withSst = isset($lineSsts[$index]) && $lineSsts[$index] == '1';

                    // Calculate line total
                    $lineTotal = $isFoc ? 0 : ($qty * $unitPrice - $discount);
                    $lineTotal = max(0, $lineTotal);

                    // Calculate SST
                    $sstAmount = ($withSst && ! $isFoc) ? ($lineTotal * $sstValue / 100) : 0;

                    $sfProduct = ServiceFormProduct::create([
                        'service_form_id' => $sf->id,
                        'product_id' => $productId,
                        'custom_desc' => $customDesc,
                        'qty' => $qty,
                        'unit_price' => $unitPrice,
                        'discount' => $discount,
                        'uom' => $uom,
                        'is_foc' => $isFoc,
                        'with_sst' => $withSst,
                        'sst_amount' => $sstAmount,
                        'sst_value' => $withSst ? $sstValue : null,
                        'remark' => $remark,
                        'sequence' => $index,
                    ]);

                    // Save warranty periods for this line item
                    if (isset($lineWarrantyPeriods[$index]) && is_array($lineWarrantyPeriods[$index])) {
                        foreach ($lineWarrantyPeriods[$index] as $wpId) {
                            if ($wpId) {
                                ServiceFormProductWarrantyPeriod::create([
                                    'service_form_product_id' => $sfProduct->id,
                                    'warranty_period_id' => $wpId,
                                ]);
                            }
                        }
                    }

                    $subtotal += $lineTotal;
                    $totalTax += $sstAmount;
                }
            }

            // Update totals
            $sf->update([
                'subtotal' => $subtotal,
                'total_tax' => $totalTax,
                'grand_total' => $subtotal + $totalTax,
            ]);

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('service_form.create'))->with('success', $message);
            }

            return redirect(route('service_form.index'))->with('success', $message);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete($id)
    {
        $id = Crypt::decrypt($id);
        $this->serviceForm::where('id', $id)->delete();

        return back()->with('success', 'Service Form deleted');
    }

    public function getInvoiceByKeyword(Request $req)
    {
        $keyword = $req->keyword;

        $invoices = Invoice::where(function ($q) use ($keyword) {
            $q->where('sku', 'like', '%'.$keyword.'%');
        })
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get()
            ->keyBy('id');

        return response()->json(['invoices' => $invoices], 200);
    }

    public function getDealerByKeyword(Request $req)
    {
        $keyword = $req->keyword;

        $dealers = Dealer::where(function ($q) use ($keyword) {
            $q->where('name', 'like', '%'.$keyword.'%')
                ->orWhere('sku', 'like', '%'.$keyword.'%')
                ->orWhere('company_name', 'like', '%'.$keyword.'%');
        })
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get()
            ->keyBy('id');

        return response()->json(['dealers' => $dealers], 200);
    }

    public function getProductChildrenByProduct(Request $req)
    {
        $productId = $req->product_id;

        $productChildren = ProductChild::where('product_id', $productId)
            ->orderBy('sku')
            ->get(['id', 'sku']);

        return response()->json(['product_children' => $productChildren]);
    }

    public function pdf($id)
    {
        $id = Crypt::decrypt($id);
        $serviceForm = $this->serviceForm::with(['customer', 'customerLocation', 'product', 'invoice', 'dealer', 'technician'])->findOrFail($id);

        // Prepare customer name
        $customerName = '';
        if ($serviceForm->customer) {
            $customerName = $serviceForm->customer->company_name ?? $serviceForm->customer->name ?? '';
        }

        // Prepare address - CustomerLocation uses address1, address2, address3, address4
        $address = '';
        if ($serviceForm->customerLocation) {
            $addressParts = array_filter([
                $serviceForm->customerLocation->address1,
                $serviceForm->customerLocation->address2,
                $serviceForm->customerLocation->address3,
                $serviceForm->customerLocation->address4,
            ]);
            $address = implode(', ', $addressParts);
        }

        // Prepare warranty status text
        $warrantyStatus = '';
        if ($serviceForm->warranty_status == ServiceForm::WARRANTY_UNDER) {
            $warrantyStatus = 'Under Warranty';
        } elseif ($serviceForm->warranty_status == ServiceForm::WARRANTY_OUT) {
            $warrantyStatus = 'Out of Warranty';
        }

        // Prepare technician name
        $technicianName = '';
        if ($serviceForm->technician) {
            $technicianName = $serviceForm->technician->name ?? '';
        }

        $pdf = Pdf::loadView('service_form.pdf.service_form', [
            'service_form' => $serviceForm,
            'customer_name' => $customerName,
            'address' => $address,
            'warranty_status' => $warrantyStatus,
            'technician_name' => $technicianName,
            'checklist_items' => ServiceForm::CHECKLIST_ITEMS,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream($serviceForm->sku.'.pdf');
    }

    public function quotationPdf($id)
    {
        $id = Crypt::decrypt($id);
        $serviceForm = $this->serviceForm::with([
            'customer',
            'customerLocation',
            'products.product',
            'products.warrantyPeriods.warrantyPeriod',
            'paymentMethod',
            'technician',
        ])->findOrFail($id);

        // Determine template based on customer's company group
        $isHiTen = $serviceForm->customer && isHiTen($serviceForm->customer->company_group);
        $template = $isHiTen
            ? 'service_form.pdf.quotation_hi_ten'
            : 'service_form.pdf.quotation_powercool';

        // Prepare customer name
        $customerName = '';
        if ($serviceForm->customer) {
            $customerName = $serviceForm->customer->company_name ?? $serviceForm->customer->name ?? '';
        }

        // Prepare address
        $address = '';
        if ($serviceForm->customerLocation) {
            $addressParts = array_filter([
                $serviceForm->customerLocation->address1,
                $serviceForm->customerLocation->address2,
                $serviceForm->customerLocation->address3,
                $serviceForm->customerLocation->address4,
            ]);
            $address = implode(', ', $addressParts);
        }

        // Get SST value
        $sstValue = Setting::where('key', Setting::SST_KEY)->value('value') ?? 0;

        $pdf = Pdf::loadView($template, [
            'service_form' => $serviceForm,
            'products' => $serviceForm->products,
            'customer' => $serviceForm->customer,
            'customer_name' => $customerName,
            'address' => $address,
            'sst_value' => $sstValue,
            'date' => now()->format('d-m-Y'),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream($serviceForm->sku.'-quotation.pdf');
    }

    public function cashSalePdf($id)
    {
        $id = Crypt::decrypt($id);
        $serviceForm = $this->serviceForm::with([
            'customer',
            'customerLocation',
            'products.product',
            'products.warrantyPeriods.warrantyPeriod',
            'paymentMethod',
            'technician',
        ])->findOrFail($id);

        // Determine template based on customer's company group
        $isHiTen = $serviceForm->customer && isHiTen($serviceForm->customer->company_group);
        $template = $isHiTen
            ? 'service_form.pdf.cash_sale_hi_ten'
            : 'service_form.pdf.cash_sale_powercool';

        // Prepare customer name
        $customerName = '';
        if ($serviceForm->customer) {
            $customerName = $serviceForm->customer->company_name ?? $serviceForm->customer->name ?? '';
        }

        // Prepare address
        $address = '';
        if ($serviceForm->customerLocation) {
            $addressParts = array_filter([
                $serviceForm->customerLocation->address1,
                $serviceForm->customerLocation->address2,
                $serviceForm->customerLocation->address3,
                $serviceForm->customerLocation->address4,
            ]);
            $address = implode(', ', $addressParts);
        }

        // Get SST value
        $sstValue = Setting::where('key', Setting::SST_KEY)->value('value') ?? 0;

        $pdf = Pdf::loadView($template, [
            'service_form' => $serviceForm,
            'products' => $serviceForm->products,
            'customer' => $serviceForm->customer,
            'customer_name' => $customerName,
            'address' => $address,
            'sst_value' => $sstValue,
            'date' => now()->format('d-m-Y'),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream($serviceForm->sku.'-cash-sale.pdf');
    }

    public function invoicePdf($id)
    {
        $id = Crypt::decrypt($id);
        $serviceForm = $this->serviceForm::with([
            'customer',
            'customerLocation',
            'products.product',
            'products.warrantyPeriods.warrantyPeriod',
            'paymentMethod',
            'technician',
        ])->findOrFail($id);

        // Determine template based on customer's company group
        $isHiTen = $serviceForm->customer && isHiTen($serviceForm->customer->company_group);
        $template = $isHiTen
            ? 'service_form.pdf.invoice_hi_ten'
            : 'service_form.pdf.invoice_powercool';

        // Prepare customer name
        $customerName = '';
        if ($serviceForm->customer) {
            $customerName = $serviceForm->customer->company_name ?? $serviceForm->customer->name ?? '';
        }

        // Prepare address
        $address = '';
        if ($serviceForm->customerLocation) {
            $addressParts = array_filter([
                $serviceForm->customerLocation->address1,
                $serviceForm->customerLocation->address2,
                $serviceForm->customerLocation->address3,
                $serviceForm->customerLocation->address4,
            ]);
            $address = implode(', ', $addressParts);
        }

        // Get SST value
        $sstValue = Setting::where('key', Setting::SST_KEY)->value('value') ?? 0;

        $pdf = Pdf::loadView($template, [
            'service_form' => $serviceForm,
            'products' => $serviceForm->products,
            'customer' => $serviceForm->customer,
            'customer_name' => $customerName,
            'address' => $address,
            'sst_value' => $sstValue,
            'date' => now()->format('d-m-Y'),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream($serviceForm->sku.'-invoice.pdf');
    }
}
