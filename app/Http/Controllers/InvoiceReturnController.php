<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Branch;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProduct;
use App\Models\DeliveryOrderProductChild;
use App\Models\EInvoice;
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
            $do_sku = $dos[$i]->sku; // which DO this line was delivered under

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
                        if ($dops[$j]->qty != null) {
                            $products[$k]->do_skus[$do_sku] = $do_sku;
                        }

                        $dopcs = $dops[$j]->children;
                        for ($l = 0; $l < count($dopcs); $l++) {
                            $pc = ProductChild::where('id', $dopcs[$l]->product_children_id)->first();
                            $pc->do_sku = $do_sku;
                            $products[$k]->children[] = $pc;
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
                    'do_skus' => $dops[$j]->qty != null ? [$do_sku => $do_sku] : [],
                ];
                // Get product children
                $dopcs = $dops[$j]->children;
                for ($k = 0; $k < count($dopcs); $k++) {
                    $pc = ProductChild::where('id', $dopcs[$k]->product_children_id)->first();
                    $pc->do_sku = $do_sku;
                    $temp['children'][] = $pc;
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
            'invoice_sku' => $inv->sku,
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
            $do_sku = optional($selected[$i]->deliveryOrder)->sku; // which DO it was delivered under
            $reason = $selected[$i]->reason;

            if ($is_raw_material) {
                $prod = Product::where('id', $selected[$i]->object_id)->first();
            } else {
                $pc = ProductChild::where('id', $selected[$i]->object_id)->first();
                $pc->selected = true;
                $pc->do_sku = $do_sku;
                $pc->reason = $reason;
                $prod = $pc->parent;
            }

            // If product appears before
            $product_appears_before = false;
            for ($j = 0; $j < count($products); $j++) {
                if ($products[$j]->product->id == $prod->id) {
                    if ($is_raw_material) {
                        $products[$j]->product->selected_qty += $selected[$i]->qty;
                        if ($do_sku) {
                            $products[$j]->do_skus[$do_sku] = $do_sku;
                        }
                        if ($reason) {
                            $products[$j]->reasons[$reason] = $reason;
                        }
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
                'do_skus' => $is_raw_material && $do_sku ? [$do_sku => $do_sku] : [],
                'reasons' => $is_raw_material && $reason ? [$reason => $reason] : [],
            ];
            if (! $is_raw_material) {
                $temp['children'][] = $pc;
            }
            $products[] = (object) $temp;
        }

        return view('invoice_return.select', [
            'invoice_id' => $inv->id,
            'invoice_sku' => $inv->sku,
            'products' => $products,
            'is_view' => true,
            // A credit note can only be raised when the invoice was submitted to
            // MyInvois (so there's an e-invoice uuid to reference) and something
            // was actually returned.
            'can_credit_note' => count($products) > 0 && $this->submittedEInvoice($inv->id) !== null,
        ]);
    }

    /**
     * Build a pre-filled credit note from an invoice's approved returns and hand
     * off to the existing note flow. Returned goods reduce the line quantities;
     * the credit-note "diff" buildNoteChanges() computes is exactly what came
     * back. The user reviews/edits the form, then submits it for admin approval
     * like any other credit note (which is where MyInvois is actually called).
     */
    public function toCreditNote(Invoice $inv)
    {
        $eInvoice = $this->submittedEInvoice($inv->id);

        if (! $eInvoice) {
            return back()->with('error', __('This invoice has no submitted e-invoice; a credit note cannot be issued.'));
        }

        $returnedBySaleProduct = $this->returnedQtyBySaleProduct($inv->id);

        if (empty($returnedBySaleProduct)) {
            return back()->with('error', __('No approved returns found for this invoice.'));
        }

        // Collect the invoice's sale-product lines from the DO graph, deduped by
        // sale product so a line spanning multiple DOs is only listed once.
        $lines = [];
        $seen = [];
        $dos = DeliveryOrder::withoutGlobalScopes()->where('invoice_id', $inv->id)->get();

        foreach ($dos as $do) {
            foreach ($do->products as $dop) {
                $sp = $dop->saleProduct;

                if (! $sp || isset($seen[$sp->id])) {
                    continue;
                }

                $seen[$sp->id] = true;
                $lines[] = [
                    'id' => $sp->id,
                    'name' => optional($sp->product)->model_desc,
                    'qty' => (int) $sp->qty,
                    'price' => $sp->unit_price,
                ];
            }
        }

        // Keep only the returned lines, with the quantity reduced to what's left
        // after the return (original - returned).
        $items = $this->reduceLinesByReturns($lines, $returnedBySaleProduct);

        if (empty($items)) {
            return back()->with('error', __('Returned items could not be matched to invoice lines.'));
        }

        // Latest return reason becomes the default note reason (the user can edit).
        $reason = ReturnProduct::where('invoice_id', $inv->id)
            ->whereNotNull('reason')->latest('id')->value('reason');

        // Prime the session keys the note flow reads (it ignores the form's hidden
        // noteType/type and uses these instead). Map the invoice's company to the
        // value the note flow expects: anything but 'powercool' is treated as Hi-Ten.
        Session::put('invoice_type', 'eInvoice');
        Session::put('fromBilling', false);
        Session::put('note_type', 'credit');
        Session::put('company', $inv->company === 'powercool' ? 'powercool' : 'hiten');

        return view('invoice.convert', [
            'step' => 6,
            'customers' => [],
            'eInvoices' => [],
            'results' => [[
                'invoice_uuid' => $eInvoice->uuid,
                'items' => $items,
            ]],
            'note_reason_default' => $reason,
        ]);
    }

    /**
     * The submitted MyInvois e-invoice (has a uuid) for an invoice, or null.
     */
    private function submittedEInvoice(int $invoiceId): ?EInvoice
    {
        return EInvoice::where('einvoiceable_type', Invoice::class)
            ->where('einvoiceable_id', $invoiceId)
            ->whereNotNull('uuid')
            ->first();
    }

    /**
     * Total returned quantity for an invoice, keyed by sale_product_id. Raw
     * materials contribute their stored qty; each returned serial child counts
     * as one unit. Returns are matched back to a sale-product line via the DO
     * they were delivered under and their (parent) product.
     */
    private function returnedQtyBySaleProduct(int $invoiceId): array
    {
        $map = [];
        $do_ids = DeliveryOrder::withoutGlobalScopes()->where('invoice_id', $invoiceId)->pluck('id');

        foreach (ReturnProduct::where('invoice_id', $invoiceId)->get() as $rp) {
            $is_raw_material = $rp->object_type == Product::class;
            $product_id = $is_raw_material
                ? $rp->object_id
                : optional(ProductChild::find($rp->object_id))->product_id;

            if (! $product_id) {
                continue;
            }

            $query = DeliveryOrderProduct::whereHas('saleProduct', function ($q) use ($product_id) {
                $q->where('product_id', $product_id);
            });

            // Prefer the exact DO the return was recorded against; fall back to
            // any DO on the invoice (older returns may not have it stored).
            if ($rp->delivery_order_id) {
                $query->where('delivery_order_id', $rp->delivery_order_id);
            } else {
                $query->whereIn('delivery_order_id', $do_ids);
            }

            $sale_product_id = $query->value('sale_product_id');

            if (! $sale_product_id) {
                continue;
            }

            $map[$sale_product_id] = ($map[$sale_product_id] ?? 0) + ($is_raw_material ? (int) $rp->qty : 1);
        }

        return $map;
    }

    /**
     * Turn invoice sale-product lines into credit-note items. Only lines that had
     * something returned are kept, and each line's quantity is reduced to what
     * remains (original - returned) so the note credits exactly what came back.
     *
     * @param  array  $lines  list of ['id', 'name', 'qty', 'price']
     * @param  array  $returnedBySaleProduct  sale_product_id => returned qty
     */
    public function reduceLinesByReturns(array $lines, array $returnedBySaleProduct): array
    {
        $items = [];

        foreach ($lines as $line) {
            if (! isset($returnedBySaleProduct[$line['id']])) {
                continue;
            }

            $items[] = [
                'product_id' => $line['id'],
                'name' => $line['name'] ?? null,
                'qty' => max(0, (int) $line['qty'] - (int) $returnedBySaleProduct[$line['id']]),
                'price' => $line['price'] ?? null,
            ];
        }

        return $items;
    }

    /**
     * Create the actual return records for an invoice from a stored payload.
     * Called once an admin approves the return request. Writes return_products
     * rows and force-deletes returned product children from the DO/SO.
     */
    public function createReturnFromPayload(int $invoiceId, array $products, ?string $reason = null): void
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
                'reason' => $reason,
                // Resolve the DO before the children are force-deleted below.
                'delivery_order_id' => $this->resolveDeliveryOrderId($invoiceId, $is_raw_material, $product['id']),
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
     * Find which delivery order an item was delivered under, within an invoice.
     * Product children resolve exactly; raw materials resolve to the first DO
     * line that carried the product (qty isn't tracked per-DO on the return).
     */
    private function resolveDeliveryOrderId(int $invoiceId, bool $is_raw_material, $object_id): ?int
    {
        $do_ids = DeliveryOrder::withoutGlobalScopes()->where('invoice_id', $invoiceId)->pluck('id');

        if (! $is_raw_material) {
            $dopc = DeliveryOrderProductChild::where('product_children_id', $object_id)->first();

            return optional(optional($dopc)->doProduct)->delivery_order_id;
        }

        $sale_product_ids = SaleProduct::where('product_id', $object_id)->pluck('id');

        return DeliveryOrderProduct::whereIn('delivery_order_id', $do_ids)
            ->whereIn('sale_product_id', $sale_product_ids)
            ->value('delivery_order_id');
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
