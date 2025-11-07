<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Production;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $req) {
        // Today Task
        $today_tasks = Task::where('start_date', now()->format('Y-m-d'))->orderBy('id', 'desc');
        
        if ($req->has('task_status') && $req->task_status != null) {
            $today_tasks = $today_tasks->where('status', $req->task_status);   
        }
        $today_tasks = $today_tasks->get();

        $task_status = [
            Task::STATUS_TO_DO => (new Task)->statusToHumanRead(Task::STATUS_TO_DO),
            Task::STATUS_DOING => (new Task)->statusToHumanRead(Task::STATUS_DOING),
            Task::STATUS_IN_REVIEW => (new Task)->statusToHumanRead(Task::STATUS_IN_REVIEW),
            Task::STATUS_COMPLETED => (new Task)->statusToHumanRead(Task::STATUS_COMPLETED),
        ];
        // Production Summary
        $production_summary = Production::orderBy('id', 'desc');

        if ($req->has('production_status') && $req->production_status != null) {
            $production_summary = $production_summary->where('status', $req->production_status);   
        }
        $production_summary = $production_summary->get();

        $production_status = [
            Production::STATUS_TO_DO => (new Production)->statusToHumanRead(Production::STATUS_TO_DO),
            Production::STATUS_DOING => (new Production)->statusToHumanRead(Production::STATUS_DOING),
            Production::STATUS_COMPLETED => (new Production)->statusToHumanRead(Production::STATUS_COMPLETED),
            Production::STATUS_TRANSFERRED => (new Production)->statusToHumanRead(Production::STATUS_TRANSFERRED),
        ];
        // Low stock
        $products = Product::with('images')->where('type', Product::TYPE_PRODUCT)->get();
        $raw_materials = Product::with('images')->where('type', Product::TYPE_RAW_MATERIAL)->get();
        // Suppliers & Customers count
        $suppliers_count = Supplier::count();
        $customers_count = Customer::count();
        // Best selling products
        $best_selling_products = [];
        $limit = 5;
        
        $temp = [];
        $sales = Sale::where('type', Sale::TYPE_SO)->get();
        for ($i=0; $i < count($sales); $i++) { 
            $sale_products = $sales[$i]->products;
            for ($j=0; $j < count($sale_products); $j++) { 
                if (!isset($temp[$sale_products[$j]->product_id])) {
                    $temp[$sale_products[$j]->product_id] = 1;
                } else {
                    $temp[$sale_products[$j]->product_id] += 1;
                }
            }
        }
        arsort($temp);

        foreach ($temp as $product_id => $count) {
            if ($limit <= 0) {
                break;
            }

            $best_selling_products[] = [
                'product' => Product::withTrashed()->where('id', $product_id)->first(),
                'count' => $count,
            ];
            $limit--;
        }

        return view('dashboard', [
            'selected_task_status' => $req->task_status,
            'selected_production_status' => $req->production_status,
            'task_status' => $task_status,
            'production_status' => $production_status,
            'today_tasks' => $today_tasks,
            'production_summary' => $production_summary,
            'products' => $products,
            'raw_materials' => $raw_materials,
            'suppliers_count' => $suppliers_count,
            'customers_count' => $customers_count,
            'best_selling_products' => $best_selling_products,
        ]);
    }
}
