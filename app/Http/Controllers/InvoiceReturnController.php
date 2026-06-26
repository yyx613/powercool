<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Branch;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProductChild;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\ReturnProduct;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class InvoiceReturnController extends Controller
{
    public function index()
    {
        $page = Session::get('invoice-return-page');

        return view('invoice_return.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function productSelection(Invoice $inv)
    {
        $products = [];
        $dos = DeliveryOrder::where('invoice_id', $inv->id)->get();

        for ($i = 0; $i < count($dos); $i++) {
            $dops = $dos[$i]->products;

            for ($j = 0; $j < count($dops); $j++) {
                $temp = [];
                $product = Product::where('id', SaleProduct::where('id', $dops[$j]->sale_product_id)->value('product_id'))->first();

                if ($dops[$j]->qty == null && count($dops[$j]->children) <= 0) {
                    continue;
                }

                // If product appears before
                $product_appears_before = false;
                for ($k = 0; $k < count($products); $k++) {
                    if ($products[$k]->product->id == $product->id) {
                        $products[$k]->qty += $dops[$j]->qty;

                        $dopcs = $dops[$j]->children;
                        for ($l = 0; $l < count($dopcs); $l++) {
                            $products[$k]->children[] = ProductChild::where('id', $dopcs[$l]->product_children_id)->first();
                        }

                        $product_appears_before = true;
                        break;
                    }
                }

                if ($product_appears_before) {
                    continue;
                }

                // Get product
                $temp = [
                    'is_raw_material' => $dops[$j]->qty != null ? true : false,
                    'qty' => $dops[$j]->qty,
                    'product' => $product,
                ];
                // Get product children
                $dopcs = $dops[$j]->children;
                for ($k = 0; $k < count($dopcs); $k++) {
                    $temp['children'][] = ProductChild::where('id', $dopcs[$k]->product_children_id)->first();
                }

                $products[] = (object) $temp;
            }
        }

        // Already returned (approved) - subtract these from what's still returnable.
        $selected = ReturnProduct::where('invoice_id', $inv->id)->get();
        foreach ($selected as $row) {
            $this->deductReturned($products, $row->object_type == Product::class, $row->object_id, $row->qty);
        }

        // Pending approval (not created yet) - lock these too so the same unit
        // can't be submitted twice before an admin approves the return.
        foreach ($this->pendingReturnItems($inv->id) as $item) {
            $this->deductReturned($products, (bool) ($item['is_raw_material'] ?? false), $item['id'] ?? null, $item['qty'] ?? 0);
        }

        return view('invoice_return.select', [
            'invoice_id' => $inv->id,
            'products' => $products,
        ]);
    }

    public function productSelectionSubmit(Request $req, Invoice $inv)
    {
        // A reason is required; the return is held for admin approval and only
        // created once approved (mirrors the credit/debit note flow).
        $validator = Validator::make($req->all(), [
            'reason' => 'required|max:1000',
        ], [], [
            'reason' => 'Reason',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $selected_products = json_decode($req->products, true);

            if (empty($selected_products)) {
                DB::rollback();

                return back()->with('error', __('No products selected'));
            }

            $description = 'Invoice Return for ' . ($inv->sku ?? ('INV #' . $inv->id))
                . ' requested by ' . (optional(Auth::user())->name ?? 'a user') . '.';

            $approval = Approval::create([
                'object_type' => Invoice::class,
                'object_id' => $inv->id,
                'status' => Approval::STATUS_PENDING_APPROVAL,
                'data' => json_encode([
                    'is_invoice_return' => true,
                    'invoice_id' => $inv->id,
                    'products' => $selected_products,
                    'reason' => trim((string) $req->reason),
                    'submitted_by' => Auth::id(),
                    'description' => $description,
                ]),
            ]);

            (new Branch)->assign(Approval::class, $approval->id);

            DB::commit();

            return redirect(route('invoice_return.index'))->with('success', __('Invoice return submitted for admin approval'));
        } catch (\Throwable $th) {
            DB::rollback();
            report($th);

            return back()->with('error', __('Something went wrong'));
        }
    }

    public function productSelectionView(Invoice $inv)
    {
        $products = [];
        $selected = ReturnProduct::where('invoice_id', $inv->id)->get();

        for ($i = 0; $i < count($selected); $i++) {
            $is_raw_material = $selected[$i]->object_type == Product::class;

            if ($is_raw_material) {
                $prod = Product::where('id', $selected[$i]->object_id)->first();
            } else {
                $pc = ProductChild::where('id', $selected[$i]->object_id)->first();
                $pc->selected = true;
                $prod = $pc->parent;
            }

            // If product appears before
            $product_appears_before = false;
            for ($j = 0; $j < count($products); $j++) {
                if ($products[$j]->product->id == $prod->id) {
                    if ($is_raw_material) {
                        $products[$j]->product->selected_qty += $selected[$i]->qty;
                    } else {
                        $products[$j]->children[] = $pc;
                    }

                    $product_appears_before = true;
                    break;
                }
            }

            if ($product_appears_before) {
                continue;
            }

            if ($is_raw_material) {
                $prod->selected_qty = ($prod->selected_qty ?? 0) + $selected[$i]->qty;
            }

            $temp = [];
            $temp = [
                'is_raw_material' => $is_raw_material,
                'qty' => $is_raw_material ? $selected[$i]->qty : null,
                'product' => $prod,
            ];
            if (! $is_raw_material) {
                $temp['children'][] = $pc;
            }
            $products[] = (object) $temp;
        }

        return view('invoice_return.select', [
            'invoice_id' => $inv->id,
            'products' => $products,
            'is_view' => true,
        ]);
    }

    /**
     * Create the actual return records for an invoice from a stored payload.
     * Called once an admin approves the return request. Writes return_products
     * rows and force-deletes returned product children from the DO/SO.
     */
    public function createReturnFromPayload(int $invoiceId, array $products): void
    {
        $now = now();
        $data = [];
        $pc_ids_to_delete = [];

        foreach ($products as $product) {
            $is_raw_material = (bool) ($product['is_raw_material'] ?? false);

            $data[] = [
                'invoice_id' => $invoiceId,
                'object_type' => $is_raw_material ? Product::class : ProductChild::class,
                'object_id' => $product['id'],
                'qty' => $is_raw_material ? $product['qty'] : null,
                'returned_at' => $now,
                'created_at' => $now,
            ];
            if (! $is_raw_material) {
                $pc_ids_to_delete[] = $product['id'];
            }
        }

        if (count($data) > 0) {
            ReturnProduct::insert($data);
        }

        // Delete pc from DO & SO
        if (count($pc_ids_to_delete) > 0) {
            DeliveryOrderProductChild::whereIn('product_children_id', $pc_ids_to_delete)->forceDelete();
            SaleProductChild::whereIn('product_children_id', $pc_ids_to_delete)->forceDelete();
        }
    }

    /**
     * Selected items sitting in still-pending invoice-return approvals for an
     * invoice, flattened into a single list of payload items.
     */
    private function pendingReturnItems(int $invoiceId): array
    {
        return Approval::where('object_type', Invoice::class)
            ->where('object_id', $invoiceId)
            ->where('status', Approval::STATUS_PENDING_APPROVAL)
            ->get()
            ->flatMap(function ($approval) {
                $payload = json_decode($approval->data, true);

                return ($payload['is_invoice_return'] ?? false) ? ($payload['products'] ?? []) : [];
            })
            ->all();
    }

    /**
     * Subtract one already-returned (or pending) item from the returnable list:
     * raw materials reduce the quantity left, product children get marked selected.
     */
    private function deductReturned(array &$products, bool $is_raw_material, $object_id, $qty): void
    {
        for ($j = 0; $j < count($products); $j++) {
            if ($is_raw_material) {
                if ($products[$j]->product->id == $object_id) {
                    $products[$j]->qty -= $qty;
                    break;
                }
            } elseif ($products[$j]->is_raw_material == false) {
                foreach ($products[$j]->children as $child) {
                    if ($child->id == $object_id) {
                        $child->selected = true;
                        break;
                    }
                }
            }
        }
    }
}
