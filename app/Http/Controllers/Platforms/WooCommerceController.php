<?php

namespace App\Http\Controllers\Platforms;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerLocation;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WooCommerceController extends Controller
{
    public function handleWooCommerceOrderCreated(Request $request){   
        $data = $request->input();
        try {
            DB::beginTransaction();
            $sale = Sale::where('order_id',$data['id'])->first();
            if($sale){
                $sale->update([
                    'status' =>  $data['status'] == 'cancelled' ?  Sale::STATUS_INACTIVE : Sale::STATUS_ACTIVE
                ]);
            }else{
                $customer = Customer::where('sku', $data['customer_id'])->first();
                if(!$customer){
                    $customer = Customer::create([
                        'sku' => $data['customer_id'],
                    ]);
                }
    
                $billAddress = $data['billing']['address_1'].$data['billing']['address_2'];
                $billingAddress = CustomerLocation::create([
                    'customer_id' => $customer->id,
                    'type' => 1, 
                    'is_default' => 1,
                    'address' => $billAddress,
                    'city' => $data['billing']['city'],
                    'state' => $data['billing']['state'], 
                    'zip_code' => $data['billing']['postcode']
                ]);
    
                $shipAddress = $data['shipping']['address_1'].$data['shipping']['address_2'];
                $shippingAddress = CustomerLocation::create([
                    'customer_id' => $customer->id,
                    'type' => 1, 
                    'is_default' => 2,
                    'address' => $shipAddress,
                    'city' => $data['shipping']['city'],
                    'state' => $data['shipping']['state'], 
                    'zip_code' => $data['shipping']['postcode']
                ]);
    
                $sale = Sale::create([
                    'order_id' => $data['id'],
                    'status' => $data['status'],
                    'platform' => 'WooCommerce',
                    'sku' => $data['order_key'],
                    'type' => 2,
                    'customer_id' => $customer->id,
                    'payment_amount'=> $data['total'],
                    'payment_method'=> $data['payment_method'],
                    'delivery_address_id'=> $shippingAddress->id,
                ]);
                
                foreach ($data['line_items'] as $item) {
                    $product = Product::where('woo_commerce_sku', $item['variation_id'])->first();
                    // $productChildren = ProductChild::where('product_id',$product->id)->where('woo_commerce_sku', $item['variation_id'])->first();
                    $saleProduct = SaleProduct::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'desc' => $item['name'] ?? null,
                        'qty' => $item['quantity'], 
                        'unit_price' => $item['price'] ?? 0,
                        'warranty_period_id' => 1
                    ]);
                }
            }
            DB::commit();
            return 'done';
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        
    }

    public function handleWooCommerceOrderUpdated(Request $request){
        $data = $request->input();
        try {
            DB::beginTransaction();
            
            $sale = Sale::where('order_id', $data['id'])->first();
            if ($sale) {
                $customer = Customer::where('sku', $data['customer_id'])->first();
                if (!$customer) {
                    $customer = Customer::create([
                        'sku' => $data['customer_id'],
                    ]);
                }

                $billingLocation = CustomerLocation::where('customer_id', $customer->id)
                ->where('type', 1) 
                ->first();

                if ($billingLocation) {
                    $billAddress = $data['billing']['address_1'] . $data['billing']['address_2'];
                    $billingLocation->update([
                        'address' => $billAddress,
                        'city' => $data['billing']['city'],
                        'state' => $data['billing']['state'],
                        'zip_code' => $data['billing']['postcode'],
                        'is_default' => 1
                    ]);
                }

                $shippingLocation = CustomerLocation::where('customer_id', $customer->id)
                ->where('type', 2) 
                ->first();
                if ($shippingLocation) {
                    $shipAddress = $data['shipping']['address_1'] . $data['shipping']['address_2'];
                    $shippingLocation->update([
                        'address' => $shipAddress,
                        'city' => $data['shipping']['city'],
                        'state' => $data['shipping']['state'],
                        'zip_code' => $data['shipping']['postcode'],
                        'is_default' => 1
                    ]);
                }
               

                $sale->update([
                    'status' => $data['status'] == 'cancelled' ?  Sale::STATUS_INACTIVE : Sale::STATUS_ACTIVE,
                    'payment_method' => $data['payment_method'],
                    'payment_amount' => $data['total'],
                ]);


                $existingSaleProducts = SaleProduct::where('sale_id', $sale->id)->get();
                
                foreach ($existingSaleProducts as $existingSaleProduct) {
                    SaleProductChild::where('sale_product_id', $existingSaleProduct->id)->forceDelete();
                    $existingSaleProduct->forceDelete();
                }


                foreach ($data['line_items'] as $item) {
                    $product = Product::where('woo_commerce_sku', $item['variation_id'])->first();
                    // $productChildren = ProductChild::where('product_id', $product->id)
                    //                             ->where('woo_commerce_sku', $item['variation_id'])
                    //                             ->first();

                    $saleProduct = SaleProduct::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'desc' => $item['name'] ?? null,
                        'qty' => $item['quantity'],
                        'unit_price' => $item['price'] ?? 0,
                        'warranty_period_id' => 1
                    ]);
                }
            }
            DB::commit();
            return 'success';
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to process WooCommerce webhook', 'message' => $e->getMessage()], 500);
        }
    }

    public function handleWooCommerceOrderDeleted(Request $request){
        $data = $request->input();
        try {
            DB::beginTransaction();
            $sale = Sale::where('order_id', $data['id'])->first();
            if($sale){
                $sale->update([
                    'status' =>  Sale::STATUS_INACTIVE
                ]);
            }
            DB::commit();
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to process WooCommerce webhook', 'message' => $e->getMessage()], 500);
        }
    }

    public function handleWooCommerceOrderRestored(Request $request){
        $data = $request->input();
        try {
            DB::beginTransaction();
            $sale = Sale::where('order_id', $data['id'])->first();
            if($sale){
                $sale->update([
                    'status' =>  Sale::STATUS_ACTIVE
                ]);
            }
            DB::commit();
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to process WooCommerce webhook', 'message' => $e->getMessage()], 500);
        }
    }

    
}

