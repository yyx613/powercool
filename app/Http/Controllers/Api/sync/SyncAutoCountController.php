<?php

namespace App\Http\Controllers\Api\sync;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\DeliveryOrder;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\GRN;
use App\Models\Currency;


class SyncAutoCountController extends Controller
{

    //update sync column to 0 for autocount to perform sync automatically, default is NULL

    public function syncCreditor(Request $request)
    {
        try {
            $data = $request->json()->all();// Retrieve all UOM records from the request
            $companyGroup = $request->query('company_group');

            foreach ($data as $record) {
                $accNo = $record['AccNo'];

                // Retrieve product_id using itemCode from products table
                $supplier = Supplier::where('sku', $accNo)->first();

                // Retrieve or create currency
                $CurrencyCode = Currency::where('name', $record['CurrencyCode'])->first();

                if (!$CurrencyCode) {
                    $currencyId = Currency::create([
                        'name' => $record['CurrencyCode'],
                        'is_active' => '1',
                        'created_at' => now(),
                        'updated_at' => now()
                    ])->id;
                } else {
                    $currencyId = $CurrencyCode->id;
                }

                if ($supplier) {
                    // Since $supplier is now an Eloquent model, we can use update()
                    $supplier->update([
                        'name' => $record['CompanyName'],
                        'phone' => $record['Phone'] ?? "-",
                        'company_name' => $record['CompanyName'],
                        'company_group' => $companyGroup,
                        'company_registration_number' => $record['RegisterNo'],
                        'location' => $record['Address1'] . ' ' . $record['Address2'] . ' ' . $record['Address3'],
                        'currency_id' => $currencyId,
                        'updated_at' => now()
                    ]);
                } else {
                    // Insert new supplier
                    Supplier::create([
                        'sku' => $record['AccNo'],
                        'name' => $record['CompanyName'],
                        'phone' => $record['Phone'] ?? "-",
                        'company_name' => $record['CompanyName'],
                        'company_group' => $companyGroup,
                        'company_registration_number' => $record['RegisterNo'],
                        'location' => $record['Address1'] . ' ' . $record['Address2'] . ' ' . $record['Address3'],
                        'currency_id' => $currencyId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
            return response()->json(['message' => 'Batch processed successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function syncDebtor(Request $request)
    {
        try {
            $data = $request->json()->all(); // Retrieve all UOM records from the request
            $companyGroup = $request->query('company_group');


            foreach ($data as $record) {
                $accNo = $record['AccNo'];

                // Retrieve product_id using itemCode from products table
                $supplier = Customer::where('sku', $accNo)->first();

                // Retrieve or create currency
                $CurrencyCode = Currency::where('name', $record['CurrencyCode'])->first();

                if (!$CurrencyCode) {
                    $currencyId = Currency::create([
                        'name' => $record['CurrencyCode'],
                        'is_active' => '1',
                        'created_at' => now(),
                        'updated_at' => now()
                    ])->id;
                } else {
                    $currencyId = $CurrencyCode->id;
                }

                if ($supplier) {
                    // Since $supplier is now an Eloquent model, we can use update()
                    $supplier->update([
                        'name' => $record['CompanyName'],
                        'phone' => $record['Phone'] ?? "-",
                        'company_name' => $record['CompanyName'],
                        'company_group' => $companyGroup,
                        'company_registration_number' => $record['RegisterNo'],
                        // 'location' => $record['Address1'] . ' ' . $record['Address2'] . ' ' . $record['Address3'],
                        'currency_id' => $currencyId,
                        'updated_at' => now()
                    ]);
                } else {
                    // Insert new supplier
                    Customer::create([
                        'sku' => $record['AccNo'],
                        'name' => $record['CompanyName'],
                        'phone' => $record['Phone'] ?? "-",
                        'company_name' => $record['CompanyName'],
                        'company_group' => $companyGroup,
                        'company_registration_number' => $record['RegisterNo'],
                        // 'location' => $record['Address1'] . ' ' . $record['Address2'] . ' ' . $record['Address3'],
                        'currency_id' => $currencyId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
            return response()->json(['message' => 'Batch processed successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function getUnsyncedSuppliers(Request $request)
    {
        $companyGroup = $request->query('company_group');

        if (!$companyGroup || !is_numeric($companyGroup)) {
            return response()->json(['error' => 'Invalid company group'], 400);
        }
    
        $suppliers = Supplier::where('sync', 0)
                             ->where('company_group', $companyGroup)
                             ->get();
    
        // Return only the array of suppliers without any wrapper
        return response()->json($suppliers);
    }


    public function updateSupplierSyncStatus(Request $request)
    {
        try {
            // Validate that the request contains an array of IDs
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer', // Each ID in the array must be an integer
            ]);

            // Update all records in one query
            $updated = Supplier::whereIn('id', $request->ids)->update(['sync' => 1]);

            // Return success message with the count of updated records
            return response()->json([
                'message' => "$updated suppliers updated successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUnsyncedCustomers(Request $request)
    {
        $companyGroup = $request->query('company_group');

        if (!$companyGroup || !is_numeric($companyGroup)) {
            return response()->json(['error' => 'Invalid company group'], 400);
        }
    
        $suppliers = Supplier::where('sync', 0)
                             ->where('company_group', $companyGroup)
                             ->get();
    
        // Return only the array of suppliers without any wrapper
        return response()->json($suppliers);
    }

    public function updateCustomerSyncStatus(Request $request)
    {
        try {
            // Validate that the request contains an array of IDs
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer', // Each ID in the array must be an integer
            ]);

            // Update all records in one query
            $updated = Supplier::whereIn('id', $request->ids)->update(['sync' => 1]);

            // Return success message with the count of updated records
            return response()->json([
                'message' => "$updated suppliers updated successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUnsyncedInvoices(Request $request)
    {
        $companyGroup = $request->query('company_group');

        if (!$companyGroup || !is_numeric($companyGroup)) {
            return response()->json(['error' => 'Invalid company group'], 400);
        }

        $data = DB::table('invoices')
            ->select(
                'invoices.id AS id', 'invoices.sku AS doc_no', 'invoices.created_at AS date', 'customers.sku AS debtor_code', 'customers.name AS debtor_name',
                'users.name AS agent', 'currencies.name AS curr_code', 'invoices.status AS status', 'invoices.filename AS filename', 'created_by.name AS created_by',
                'invoices.company AS company_group'
            )
            ->where('sales.type', Sale::TYPE_SO)
            ->where('invoices.company',$companyGroup)
            ->where('invoices.sync',0)
            ->leftJoin('delivery_orders', 'invoices.id', '=', 'delivery_orders.invoice_id')
            ->leftJoin('sales', DB::raw('FIND_IN_SET(delivery_orders.id, sales.convert_to)'), '>', DB::raw("'0'"))
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->leftJoin('currencies', 'customers.currency_id', '=', 'currencies.id')
            ->leftJoin('users', 'users.id', '=', 'sales.sale_id')
            ->leftJoin('users AS created_by', 'created_by.id', '=', 'delivery_orders.created_by')
            ->groupBy('invoices.id');

        // Order
        if ($request->has('order')) {
            $map = [
                1 => 'invoices.sku',
                2 => 'invoices.created_at',
                3 => 'customers.sku',
                5 => 'customers.name',
                6 => 'users.name',
            ];
            foreach ($request->order as $order) {
                $data = $data->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $data = $data->orderBy('invoices.id', 'desc');
        }

        $records_count = $data->count();
        $records_ids = $data->pluck('id');
        $records_paginator = $data->simplePaginate(10);

        $data = [
            'data' => [],
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $record) {
            $dos = DeliveryOrder::where('invoice_id', $record->id)->get();
            $total_amount = 0;
            $do_skus = [];

            for ($i = 0; $i < count($dos); $i++) {
                $sos = Sale::where('type', Sale::TYPE_SO)->whereRaw("find_in_set('".$dos[$i]->id."', convert_to)")->get();
                for ($j = 0; $j < count($sos); $j++) {
                    $total_amount += $sos[$j]->getTotalAmount();
                }
                $do_skus[] = $dos[$i]->sku;
            }

            $data['data'][] = [
                'id' => $record->id,
                'doc_no' => $record->doc_no,
                'date' => Carbon::parse($record->date)->format('d M Y'),
                'debtor_code' => $record->debtor_code,
                'transfer_from' => implode(', ', $do_skus),
                'debtor_name' => $record->debtor_name,
                'agent' => $record->agent ?? null,
                'curr_code' => $record->curr_code ?? null,
                'total' => number_format($total_amount, 2),
                'created_by' => $record->created_by ?? null,
                'company_group' => $record->company_group,
                'status' => $record->status,
                'filename' => $record->filename,
            ];
        }
    
        return response()->json($data);
    }

    public function updateInvoiceSyncStatus(Request $request)
    {
        try {
            // Validate that the request contains an array of IDs
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer', // Each ID in the array must be an integer
            ]);

            // Update all records in one query
            $updated = Invoice::whereIn('id', $request->ids)->update(['sync' => 1]);

            // Return success message with the count of updated records
            return response()->json([
                'message' => "$updated Invoices updated successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUnsyncedProducts(Request $request)
    {
        $companyGroup = $request->query('company_group');

        if (!$companyGroup || !is_numeric($companyGroup)) {
            return response()->json(['error' => 'Invalid company group'], 400);
        }
    
        $products = Product::where('sync', 0)
                             ->where('company_group', $companyGroup)
                             ->get();
    
        return response()->json($products);
    }

    public function updateProductSyncStatus(Request $request)
    {
        try {
            // Validate that the request contains an array of IDs
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer', // Each ID in the array must be an integer
            ]);

            // Update all records in one query
            $updated = Supplier::whereIn('id', $request->ids)->update(['sync' => 1]);

            // Return success message with the count of updated records
            return response()->json([
                'message' => "$updated suppliers updated successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUnsyncedGrns(Request $request)
    {
        $companyGroup = $request->query('company_group');

        if (!$companyGroup || !is_numeric($companyGroup)) {
            return response()->json(['error' => 'Invalid company group'], 400);
        }
    
        $products = DB::table('grn as g')
        ->select('g.*','p.sku as item_code','p.model_desc','g.sku as doc_no','credit_terms.name as display_term','s.name as creditor_name','s.sku as creditor_code')
        ->where('g.sync', 0)
        ->where('g.company_group', $companyGroup)
        ->leftJoin('credit_terms', 'credit_terms.id', '=', 'g.term')
        ->leftJoin('suppliers as s', 's.id', '=', 'g.supplier_id')
        ->leftJoin('products as p', 'p.id', '=', 'g.product_id')
        ->get();

        $sum_total = DB::table('grn as g')
        ->select(
            'g.sku as doc_no',
            DB::raw('SUM(g.total_price) as total_price_sum') // Sum up total_price for the same sku
        )
        ->where('g.sync', 0)
        ->where('g.company_group', $companyGroup)
        ->leftJoin('credit_terms', 'credit_terms.id', '=', 'g.term')
        ->leftJoin('suppliers as s', 's.id', '=', 'g.supplier_id')
        ->leftJoin('products as p', 'p.id', '=', 'g.product_id')
        ->groupBy('g.sku') 
        ->get();
    

        $data = [
            'data' => $products,
            'sumTotal' => $sum_total,
        ];
    
        return response()->json($data);
    }

    public function updateGrnsSyncStatus(Request $request)
    {
        try {
            // Validate that the request contains an array of IDs
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer', // Each ID in the array must be an integer
            ]);

            // Update all records in one query
            $updated = GRN::whereIn('id', $request->ids)->update(['sync' => 1]);

            // Return success message with the count of updated records
            return response()->json([
                'message' => "$updated Grns updated successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
