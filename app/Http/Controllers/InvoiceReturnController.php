<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProductChild;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\ReturnProduct;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

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

        // Returned
        $selected = ReturnProduct::where('invoice_id', $inv->id)->get();

        for ($i = 0; $i < count($selected); $i++) {
            $is_raw_material = $selected[$i]->object_type == Product::class;

            for ($j = 0; $j < count($products); $j++) {
                if ($is_raw_material) {
                    if ($products[$j]->product->id == $selected[$i]->object_id) {
                        $products[$j]->qty -= $selected[$i]->qty;
                        break;
                    }
                } elseif ($products[$j]->is_raw_material == false) {
                    for ($k = 0; $k < count($products[$j]->children); $k++) {
                        if ($products[$j]->children[$k]->id == $selected[$i]->object_id) {
                            $products[$j]->children[$k]->selected = true;
                            break;
                        }
                    }
                }
            }
        }

        return view('invoice_return.select', [
            'invoice_id' => $inv->id,
            'products' => $products,
        ]);
    }

    public function productSelectionSubmit(Request $req, Invoice $inv)
    {
        try {
            DB::beginTransaction();

            $selected_products = json_decode($req->products);

            $now = now();
            $data = [];
            $pc_ids_to_delete = [];
            for ($i = 0; $i < count($selected_products); $i++) {
                $data[] = [
                    'invoice_id' => $inv->id,
                    'object_type' => $selected_products[$i]->is_raw_material ? Product::class : ProductChild::class,
                    'object_id' => $selected_products[$i]->id,
                    'qty' => $selected_products[$i]->is_raw_material ? $selected_products[$i]->qty : null,
                    'returned_at' => $now,
                    'created_at' => $now,
                ];
                if (! $selected_products[$i]->is_raw_material) {
                    $pc_ids_to_delete[] = $selected_products[$i]->id;
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

            DB::commit();

            return redirect(route('invoice_return.index'))->with('success', 'Products returned');
        } catch (\Throwable $th) {
            DB::rollback();
            dd($th);
            report($th);

            return back()->with('error', 'Something went wrong');
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
}
