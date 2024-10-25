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
use Illuminate\Support\Facades\Log;

class WooCommerceController extends Controller
{
    public function handleWooCommerceOrderCreated(Request $request){   
        $data = $request->input();
        Log::info('Received WooCommerce order', ['order_id' => $data['id'], 'status' => $data['status']]);

        try {
            DB::beginTransaction();
            $sale = Sale::where('order_id',$data['id'])->first();
            if($sale){
                $sale->update([
                    'status' =>  $data['status'] == 'cancelled' ?  Sale::STATUS_INACTIVE : Sale::STATUS_ACTIVE
                ]);
                Log::info('Updated existing sale status', ['order_id' => $data['id'], 'new_status' => $sale->status]);
            }else{
                $customer = Customer::where('sku', $data['customer_id'])->first();
                if(!$customer){
                    $customer = Customer::create([
                        'sku' => $data['customer_id'],
                    ]);
                    Log::info('Created new customer', ['customer_id' => $customer->id]);
                }

                $shipAddress = $data['shipping']['address_1'].$data['shipping']['address_2'];
                $existingShippingAddress = CustomerLocation::where('customer_id', $customer->id)
                    ->where('type', 2) 
                    ->where('address', $shipAddress)
                    ->where('city', $data['shipping']['city'])
                    ->where('state', $data['shipping']['state']) 
                    ->where('zip_code', $data['shipping']['postcode'])
                    ->first();

                if (!$existingShippingAddress) {
                    $shippingAddress = CustomerLocation::create([
                        'customer_id' => $customer->id,
                        'type' => 1, 
                        'is_default' => 2,
                        'address' => $shipAddress,
                        'city' => $data['shipping']['city'],
                        'state' => $data['shipping']['state'], 
                        'zip_code' => $data['shipping']['postcode']
                    ]);
                    Log::info('Created new shipping address', ['address_id' => $shippingAddress->id]);
                }else {
                    $shippingAddress = $existingShippingAddress;
                    Log::info('Found existing shipping address', ['address_id' => $shippingAddress->id]);
                }

                $billAddress = $data['billing']['address_1'].$data['billing']['address_2'];
                $existingBillingAddress = CustomerLocation::where('customer_id', $customer->id)
                    ->where('type', 1) 
                    ->where('address', $billAddress)
                    ->where('city', $data['billing']['city'])
                    ->where('state', $data['billing']['state']) 
                    ->where('zip_code', $data['billing']['postcode'])
                    ->first();

                if (!$existingBillingAddress) {
                    $billingAddress = CustomerLocation::create([
                        'customer_id' => $customer->id,
                        'type' => 1, 
                        'is_default' => 1,
                        'address' => $billAddress,
                        'city' => $data['billing']['city'],
                        'state' => $data['billing']['state'], 
                        'zip_code' => $data['billing']['postcode']
                    ]);
                    Log::info('Created new billing address', ['address_id' => $billingAddress->id]);
                }
    
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

                Log::info('Created new sale record', ['sale_id' => $sale->id, 'payment_amount' => $data['total']]);
                
                foreach ($data['line_items'] as $item) {
                    $product = Product::where('woo_commerce_sku', $item['variation_id'])->first();
                    $saleProduct = SaleProduct::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'desc' => $item['name'] ?? null,
                        'qty' => $item['quantity'], 
                        'unit_price' => $item['price'] ?? 0,
                        'warranty_period_id' => 1
                    ]);
                    Log::info('Created sale product', ['sale_id' => $sale->id, 'product_id' => $product->id, 'qty' => $item['quantity']]);
                }
            }
            DB::commit();
            Log::info('WooCommerce order create successfully processed', ['order_id' => $data['id']]);
            return 'done';
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error handling WooCommerce order creation', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to process WooCommerce webhook', 'message' => $e->getMessage()], 500);
        }
        
    }

    public function handleWooCommerceOrderUpdated(Request $request){
        $data = $request->input();
        try {
            DB::beginTransaction();

            Log::info('WooCommerce order update received', ['order_id' => $data['id']]);
            
            $sale = Sale::where('order_id', $data['id'])->first();
            if ($sale) {
                Log::info('Sale record found for order', ['order_id' => $data['id'], 'sale_id' => $sale->id]);

                $customer = Customer::where('sku', $data['customer_id'])->first();
                if (!$customer) {
                    $customer = Customer::create([
                        'sku' => $data['customer_id'],
                    ]);
                    Log::info('Customer created', ['customer_id' => $customer->id]);
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
                    Log::info('Billing location updated', ['customer_id' => $customer->id]);
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
                    Log::info('Shipping location updated', ['customer_id' => $customer->id]);
                }

                $sale->update([
                    'status' => $data['status'] == 'cancelled' ?  Sale::STATUS_INACTIVE : Sale::STATUS_ACTIVE,
                    'payment_method' => $data['payment_method'],
                    'payment_amount' => $data['total'],
                ]);
                Log::info('Sale record updated', ['sale_id' => $sale->id, 'status' => $data['status']]);

                $existingSaleProducts = SaleProduct::where('sale_id', $sale->id)->get();
                
                foreach ($existingSaleProducts as $existingSaleProduct) {
                    SaleProductChild::where('sale_product_id', $existingSaleProduct->id)->forceDelete();
                    $existingSaleProduct->forceDelete();
                    Log::info('Existing sale product deleted', ['sale_product_id' => $existingSaleProduct->id]);
                }

                foreach ($data['line_items'] as $item) {
                    $product = Product::where('woo_commerce_sku', $item['variation_id'])->first();
                    $saleProduct = SaleProduct::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'desc' => $item['name'] ?? null,
                        'qty' => $item['quantity'],
                        'unit_price' => $item['price'] ?? 0,
                        'warranty_period_id' => 1
                    ]);

                    Log::info('Sale product created', ['sale_product_id' => $saleProduct->id, 'product_id' => $product->id]);
                }
            }
            DB::commit();
            Log::info('WooCommerce order update successfully processed', ['order_id' => $data['id']]);
            return 'success';
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process WooCommerce webhook', [
                'order_id' => $data['id'],
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to process WooCommerce webhook', 'message' => $e->getMessage()], 500);
        }
    }

    public function handleWooCommerceOrderDeleted(Request $request){
        $data = $request->input();
        try {
            DB::beginTransaction();

            Log::info('WooCommerce order deletion received', ['order_id' => $data['id']]);

            $sale = Sale::where('order_id', $data['id'])->first();
            if($sale){
                $sale->update([
                    'status' =>  Sale::STATUS_INACTIVE
                ]);
                Log::info('Sale status updated to inactive', ['sale_id' => $sale->id]);
            }else {
                Log::warning('Sale not found for WooCommerce order deletion', ['order_id' => $data['id']]);
            }

            DB::commit();
            Log::info('WooCommerce order deletion processed successfully', ['order_id' => $data['id']]);

            return response()->json(['message' => 'Order deleted processed successfully'], 200);
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process WooCommerce order deletion webhook', [
                'order_id' => $data['id'],
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to process WooCommerce webhook', 'message' => $e->getMessage()], 500);
        }
    }

    public function handleWooCommerceOrderRestored(Request $request){
        $data = $request->input();
        try {
            DB::beginTransaction();

            Log::info('WooCommerce order restoration received', ['order_id' => $data['id']]);

            $sale = Sale::where('order_id', $data['id'])->first();
            if($sale){
                $sale->update([
                    'status' =>  Sale::STATUS_ACTIVE
                ]);
                Log::info('Sale status updated to active', ['sale_id' => $sale->id]);
            } else {
                Log::warning('Sale not found for WooCommerce order restoration', ['order_id' => $data['id']]);
            }

            DB::commit();
            Log::info('WooCommerce order restoration processed successfully', ['order_id' => $data['id']]);

            return response()->json(['message' => 'Order restoration processed successfully'], 200);
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process WooCommerce order restoration webhook', [
                'order_id' => $data['id'],
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to process WooCommerce webhook', 'message' => $e->getMessage()], 500);
        }
    }

    
}

