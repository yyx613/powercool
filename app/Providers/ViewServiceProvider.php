<?php

namespace App\Providers;

use App\Models\Area;
use App\Models\Branch;
use App\Models\ClassificationCode;
use App\Models\CreditTerm;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerSaleAgent;
use App\Models\Dealer;
use App\Models\DebtorType;
use App\Models\DeliveryOrder;
use App\Models\Factory;
use App\Models\InventoryCategory;
use App\Models\InventoryType;
use App\Models\Invoice;
use App\Models\MaterialUse;
use App\Models\Milestone;
use App\Models\MsicCode;
use App\Models\PaymentMethod;
use App\Models\Platform;
use App\Models\Priority;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\ProductCost;
use App\Models\Production;
use App\Models\ProductionRequestMaterial;
use App\Models\ProjectType;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use App\Models\SalesAgent;
use App\Models\Scopes\BranchScope;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Ticket;
use App\Models\UOM;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleService;
use App\Models\WarrantyPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewView;
use Spatie\Permission\Models\Permission;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('role_management.form', function (ViewView $view) {
            $permissions = Permission::get();
            // Format permissions into group
            $permissions_group = [
                'notification' => [],
                'approval' => [],
                'dashboard' => [],
                'inventory.view_action' => [],
                'inventory.summary' => [],
                'inventory.category' => [],
                'inventory.product' => [],
                'inventory.raw_material' => [],
                'inventory.raw_material_request' => [],
                'grn' => [],
                'service_reminder' => [],
                'service_history' => [],
                'warranty' => [],
                'sale.quotation' => [],
                'sale.sale_order' => [],
                'sale.delivery_order' => [],
                'sale.transport_acknowledgement' => [],
                'sale.invoice' => [],
                'sale.draft_e_invoice' => [],
                'sale.e_invoice' => [],
                'sale.target' => [],
                'sale.billing' => [],
                'sale.invoice_return' => [],
                'task.driver' => [],
                'task.technician' => [],
                'task.sale' => [],
                'production' => [],
                'production_material' => [],
                'production_request' => [],
                'ticket' => [],
                'customer' => [],
                'supplier' => [],
                'dealer' => [],
                'agent_debtor' => [],
                'vehicle' => [],
                'report' => [],
                'user_role_management' => [],
                'setting' => [],
            ];

            for ($i = 0; $i < count($permissions); $i++) {
                if (str_contains($permissions[$i], 'notification')) {
                    array_push($permissions_group['notification'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'dashboard')) {
                    array_push($permissions_group['dashboard'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'approval')) {
                    array_push($permissions_group['approval'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'inventory.summary')) {
                    array_push($permissions_group['inventory.summary'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'inventory.view_action')) {
                    array_push($permissions_group['inventory.view_action'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'inventory.category')) {
                    array_push($permissions_group['inventory.category'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'inventory.product')) {
                    array_push($permissions_group['inventory.product'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'inventory.raw_material_request')) {
                    array_push($permissions_group['inventory.raw_material_request'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'inventory.raw_material')) {
                    array_push($permissions_group['inventory.raw_material'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'grn')) {
                    array_push($permissions_group['grn'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'service_reminder')) {
                    array_push($permissions_group['service_reminder'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'service_history')) {
                    array_push($permissions_group['service_history'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'warranty')) {
                    array_push($permissions_group['warranty'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'sale.quotation')) {
                    array_push($permissions_group['sale.quotation'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'sale.sale_order')) {
                    array_push($permissions_group['sale.sale_order'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'sale.delivery_order')) {
                    array_push($permissions_group['sale.delivery_order'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'sale.transport_acknowledgement')) {
                    array_push($permissions_group['sale.transport_acknowledgement'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'sale.invoice_return')) {
                    array_push($permissions_group['sale.invoice_return'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'sale.invoice')) {
                    array_push($permissions_group['sale.invoice'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'sale.draft_e_invoice')) {
                    array_push($permissions_group['sale.draft_e_invoice'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'sale.e_invoice')) {
                    array_push($permissions_group['sale.e_invoice'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'sale.target')) {
                    array_push($permissions_group['sale.target'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'sale.billing')) {
                    array_push($permissions_group['sale.billing'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'task_driver')) {
                    array_push($permissions_group['task.driver'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'task_technician')) {
                    array_push($permissions_group['task.technician'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'task_sale')) {
                    array_push($permissions_group['task.sale'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'production_request')) {
                    array_push($permissions_group['production_request'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'production_material')) {
                    array_push($permissions_group['production_material'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'production')) {
                    array_push($permissions_group['production'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'ticket')) {
                    array_push($permissions_group['ticket'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'customer')) {
                    array_push($permissions_group['customer'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'supplier')) {
                    array_push($permissions_group['supplier'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'dealer')) {
                    array_push($permissions_group['dealer'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'agent_debtor')) {
                    array_push($permissions_group['agent_debtor'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'vehicle')) {
                    array_push($permissions_group['vehicle'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'report')) {
                    array_push($permissions_group['report'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'user_role_management')) {
                    array_push($permissions_group['user_role_management'], $permissions[$i]);
                } elseif (str_contains($permissions[$i], 'setting')) {
                    array_push($permissions_group['setting'], $permissions[$i]);
                }
            }

            $view->with('permissions_group', $permissions_group);
        });
        View::composer(['task.form', 'task.view'], function (ViewView $view) {
            if (str_contains(Route::currentRouteName(), '.technician.')) {
                $for_role = 'technician';
            } elseif (str_contains(Route::currentRouteName(), '.sale.')) {
                $for_role = 'sale';
            } elseif (str_contains(Route::currentRouteName(), '.driver.')) {
                $for_role = 'driver';
            }

            $view->with([
                'for_role' => $for_role,
            ]);
        });
        View::composer(['task.form'], function (ViewView $view) {
            $is_edit = false;
            if (str_contains(Route::currentRouteName(), '.edit')) {
                $is_edit = true;
            }

            if (str_contains(Route::currentRouteName(), '.technician.')) {
                $users = User::whereHas('roles', function ($q) {
                    $q->where('id', Role::TECHNICIAN);
                })->orderBy('id', 'desc')->get();
                $milestones = Milestone::where(function ($q) {
                    $q->where('type', Milestone::TYPE_SERVICE_TASK)->orWhere('type', Milestone::TYPE_INSTALLER_TASK);
                });

                $form_route_name = 'task.technician.store';
                if ($is_edit) {
                    $form_route_name = 'task.technician.update';
                } else {
                    $milestones->where('is_custom', false);
                }
            } elseif (str_contains(Route::currentRouteName(), '.sale.')) {
                $users = User::whereHas('roles', function ($q) {
                    $q->where('id', Role::SALE);
                })->orderBy('id', 'desc')->get();
                $milestones = Milestone::where('type', Milestone::TYPE_SITE_VISIT);

                $form_route_name = 'task.sale.store';
                if ($is_edit) {
                    $form_route_name = 'task.sale.update';
                } else {
                    $milestones->where('is_custom', false);
                }
            } elseif (str_contains(Route::currentRouteName(), '.driver.')) {
                $users = User::whereHas('roles', function ($q) {
                    $q->where('id', Role::DRIVER);
                })->orderBy('id', 'desc')->get();
                $milestones = Milestone::where('type', Milestone::TYPE_DRIVER_TASK);

                $form_route_name = 'task.driver.store';
                if ($is_edit) {
                    $form_route_name = 'task.driver.update';
                } else {
                    $milestones->where('is_custom', false);
                }
            }
            $milestones = $milestones->get();

            $tickets = Ticket::orderBy('id', 'desc')->get();

            if ($is_edit) {
                $customers = Customer::orderBy('id', 'desc')->get();
            } else {
                $customers = Customer::orderBy('id', 'desc')->where('status', Customer::STATUS_ACTIVE)->get();
            }

            // Return data
            if (str_contains(Route::currentRouteName(), '.technician.')) {
                $services = Service::where('is_active', true)->orderBy('id', 'desc')->get();
                $sale_orders = Sale::where('type', Sale::TYPE_SO)->orderBy('id', 'desc')->get();
                $sale_products = SaleProduct::with('product.children')->orderBy('id', 'desc')->get();

                $view->with([
                    'services' => $services,
                    'sale_orders' => $sale_orders,
                    'sale_products' => $sale_products,
                    'task_types' => [
                        Milestone::TYPE_SERVICE_TASK => 'Service',
                        Milestone::TYPE_INSTALLER_TASK => 'Installer',
                    ],
                ]);
            }
            if (str_contains(Route::currentRouteName(), '.driver.')) {
                $view->with([
                    'task_types' => [
                        Milestone::TYPE_DRIVER_TASK => 'Delivery',
                        Milestone::TYPE_DRIVER_OTHER_TASK => 'Others',
                    ],
                ]);
            }

            $view->with([
                'form_route_name' => $form_route_name,
                'tickets' => $tickets,
                'milestones' => $milestones,
                'users' => $users,
                'customers' => $customers,
            ]);
        });
        View::composer(['ticket.form'], function (ViewView $view) {
            $is_edit = false;
            if (str_contains(Route::currentRouteName(), '.edit')) {
                $is_edit = true;
            }
            if (isSalesOnly()) {
                $sales_agents_ids = DB::table('sales_sales_agents')->where('sales_id', Auth::user()->id)->pluck('sales_agent_id')->toArray();
                $customer_ids = CustomerSaleAgent::whereIn('sales_agent_id', $sales_agents_ids)->pluck('customer_id')->toArray();
            }
            if ($is_edit) {
                if (isset($customer_ids)) {
                    $customers = Customer::with('creditTerms.creditTerm', 'salesAgents')->whereIn('id', $customer_ids)->orderBy('id', 'desc')->get();
                } else {
                    $customers = Customer::with('creditTerms.creditTerm', 'salesAgents')->orderBy('id', 'desc')->get();
                }
            } else {
                if (isset($customer_ids)) {
                    $customers = Customer::with('creditTerms.creditTerm', 'salesAgents')->whereIn('id', $customer_ids)->orderBy('id', 'desc')->whereIn('status', [Customer::STATUS_ACTIVE, Customer::STATUS_APPROVAL_APPROVED])->get();
                } else {
                    $customers = Customer::with('creditTerms.creditTerm', 'salesAgents')->orderBy('id', 'desc')->whereIn('status', [Customer::STATUS_ACTIVE, Customer::STATUS_APPROVAL_APPROVED])->get();
                }
            }

            $view->with('customers', $customers);
        });
        View::composer(['ticket.form'], function (ViewView $view) {
            $sale_orders = Sale::where('type', Sale::TYPE_SO)->orderBy('id', 'desc')->get();
            $invoices = Invoice::orderBy('id', 'desc')->get();
            $products = Product::get();
            $product_children = ProductChild::get();

            $view->with([
                'sale_orders' => $sale_orders,
                'invoices' => $invoices,
                'products' => $products,
                'product_children' => $product_children,
            ]);
        });
        View::composer(['target.form'], function (ViewView $view) {
            $sales = User::whereHas('roles', function ($q) {
                $q->where('id', Role::SALE);
            })->orderBy('id', 'desc')->get();

            $view->with('sales', $sales);
        });
        View::composer(['quotation.form_step.quotation_details', 'sale_order.form_step.quotation_details', 'supplier.form'], function (ViewView $view) {
            $sales_agents = SalesAgent::orderBy('name', 'desc')->get();

            $view->with('sales_agents', $sales_agents);
        });

        View::composer(['sale_order.form_step.delivery_schedule', 'components.app.modal.transfer-modal'], function (ViewView $view) {
            $drivers = User::whereHas('roles', function ($q) {
                $q->where('id', Role::DRIVER);
            })->orderBy('id', 'desc')->get();

            $view->with('drivers', $drivers);
        });
        View::composer(['customer.form', 'customer.link', 'supplier.form', 'quotation.form_step.customer_details'], function (ViewView $view) {
            $prefix = [
                'mr' => 'Mr',
                'mrs' => 'Mrs',
                'ms' => 'Ms',
                'miss' => 'Miss',
            ];

            $view->with([
                'prefix' => $prefix,
            ]);
        });
        View::composer(['user_management.form', 'components.app.modal.convert-ticket-modal'], function (ViewView $view) {
            $roles = Role::get();

            $view->with([
                'roles' => $roles,
            ]);
        });
        View::composer(['quotation.form_step.product_details', 'sale_order.form_step.product_details'], function (ViewView $view) {
            $sst = Setting::where('key', Setting::SST_KEY)->value('value');
            $involved_pc_ids = getInvolvedProductChild();

            // Exclude current sale, if edit
            if (str_contains(Route::currentRouteName(), '.edit')) {
                $sale = request()->route()->parameter('sale');
                $sp_ids = $sale->products()->pluck('id')->toArray();
                $pc_for_sale = SaleProductChild::whereIn('sale_product_id', $sp_ids)->pluck('product_children_id')->toArray();

                $involved_pc_ids = array_diff($involved_pc_ids, $pc_for_sale);
            }

            $productCursor = Product::with(['children' => function ($q) use ($involved_pc_ids) {
                $q->whereNull('status')->whereNotIn('id', $involved_pc_ids);
            }])
                ->with('sellingPrices')
                ->where('is_active', true)
                ->orderBy('id', 'desc')
                ->lazy();

            $products = collect();
            foreach ($productCursor as $val) {
                $products->add($val);
            }

            // Warranty Periods
            $wps = WarrantyPeriod::where('is_active', true)->orderBy('id', 'desc')->get();
            // Promotions
            $promotions = Promotion::orderBy('id', 'desc')
                ->where('status', 1)
                ->where(function ($q) {
                    $q->orWhereNull('valid_till')
                        ->orWhere('valid_till', '>=', now()->format('Y-m-d'));
                })
                ->get();

            // UOM
            $uoms = UOM::where('is_active', true)->orderBy('id', 'desc')->get();

            if (str_contains(Route::currentRouteName(), 'quotation.')) {
                $products = $products->keyBy('id')->all();
            }

            $view->with([
                'products' => $products,
                'warranty_periods' => $wps,
                'promotions' => $promotions,
                'uoms' => $uoms,
                'customize_product_ids' => getCustomizeProductIds(),
                'sst' => $sst,
            ]);
        });
        View::composer(['inventory.form'], function (ViewView $view) {
            $suppliers = Supplier::where('is_active', true)->orderBy('id', 'desc')->get();
            $inv_cats = InventoryCategory::where('is_active', true)->orderBy('id', 'desc')->get();
            $uoms = UOM::where('is_active', true)->orderBy('id', 'desc')->get();
            $classificationCodes = ClassificationCode::withoutGlobalScope(BranchScope::class)->get();

            if (str_contains(Route::currentRouteName(), 'raw_material.')) {
                $its = InventoryType::where('is_active', true)->where('type', InventoryType::TYPE_RAW_MATERIAL)->get();
            } else {
                $its = InventoryType::where('is_active', true)->where('type', InventoryType::TYPE_PRODUCT)->get();

                $hi_ten_products = Product::where('type', Product::TYPE_PRODUCT)->where('company_group', 2)->latest()->get();
                $view->with('hi_ten_products', $hi_ten_products);
            }
            $inventory_types = $its->mapWithKeys(function ($item) {
                return [$item->id => $item->name];
            })->all();

            $view->with([
                'inv_cats' => $inv_cats,
                'suppliers' => $suppliers,
                'uoms' => $uoms,
                'classificationCodes' => $classificationCodes,
                'inventory_types' => $inventory_types,
            ]);
        });
        View::composer(['inventory.list', 'inventory.form', 'inventory.view', 'components.app.modal.stock-in-modal', 'components.app.modal.stock-out-modal'], function (ViewView $view) {
            $is_product = true;
            $is_production = false;
            if (str_contains(Route::currentRouteName(), 'production_finish_good.') || str_contains(Route::currentRouteName(), 'production_material.')) {
                $is_production = true;
            } elseif (str_contains(Route::currentRouteName(), 'raw_material.')) {
                $is_product = false;
            }
            if (str_contains(Route::currentRouteName(), 'production_material.')) {
                $is_product = false;
            }

            $view->with([
                'is_product' => $is_product,
                'is_production' => $is_production,
            ]);
        });
        View::composer(['components.app.modal.stock-out-modal'], function (ViewView $view) {
            $customers = Customer::orderBy('id', 'desc')->get();
            $technicians = User::whereHas('roles', function ($q) {
                $q->where('id', Role::TECHNICIAN);
            })->orderBy('id', 'desc')->get();

            $view->with([
                'customers' => $customers,
                'technicians' => $technicians,
            ]);
        });
        View::composer(['production.form'], function (ViewView $view) {
            $req = app(\Illuminate\Http\Request::class);
            // $milestones = Milestone::where('type', Milestone::TYPE_PRODUCTION)->get();
            // $material_uses = MaterialUse::with('materials.material')->get();
            $sales = Sale::with('products')->where('type', Sale::TYPE_SO)->orderBy('id', 'desc')->get();
            $priorities = Priority::orderBy('id', 'desc')->get();

            $users = User::whereHas('roles', function ($q) {
                $q->where('id', Role::PRODUCTION_WORKER);
            })->orderBy('id', 'desc')->get();

            if ($req->product_id != null) {
                $products = Product::where('id', $req->product_id)
                    ->withCount('materialUse')
                    ->having('material_use_count', '>', 0)
                    ->get();
            } else {
                $products = Product::where('type', Product::TYPE_PRODUCT)
                    ->orWhere(function ($q) {
                        $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', true);
                    })
                    ->withCount('materialUse')
                    ->having('material_use_count', '>', 0)
                    ->orderBy('id', 'desc')
                    ->get();
            }

            $view->with('users', $users);
            // $view->with('milestones', $milestones);
            $view->with('products', $products);
            $view->with('sales', $sales);
            $view->with('sales', $sales);
            $view->with('priorities', $priorities);
            // $view->with('material_uses', $material_uses);
        });
        View::composer(['material_use.form'], function (ViewView $view) {
            $products = Product::where('type', Product::TYPE_PRODUCT)->orderBy('id', 'desc')->get();
            $materials = Product::where('type', Product::TYPE_RAW_MATERIAL)->orderBy('id', 'desc')->get();

            $view->with('products', $products);
            $view->with('materials', $materials);
        });
        View::composer(['components.app.language-selector'], function (ViewView $view) {
            $languages = [
                'English' => 'English',
                'Chinese' => 'Chinese',
                'Bangla' => 'Bangla',
                'Malay' => 'Malay',
                'Myanmar' => 'Myanmar',
                'Nepali' => 'Nepali',
            ];

            $view->with('languages', $languages);
        });
        View::composer(['layouts.app'], function (ViewView $view) {
            $can_view_approval = hasPermission('approval.view');

            $view->with('can_view_approval', $can_view_approval);
        });
        View::composer(['layouts.navbar'], function (ViewView $view) {
            $branches = [
                Branch::LOCATION_EVERY => (new Branch)->keyToLabel(Branch::LOCATION_EVERY),
                Branch::LOCATION_KL => (new Branch)->keyToLabel(Branch::LOCATION_KL),
                Branch::LOCATION_PENANG => (new Branch)->keyToLabel(Branch::LOCATION_PENANG),
            ];

            $view->with('branches', $branches);
        });
        View::composer(['components.app.modal.create-customer-link-modal'], function (ViewView $view) {
            $branches = [
                Branch::LOCATION_KL => (new Branch)->keyToLabel(Branch::LOCATION_KL),
                Branch::LOCATION_PENANG => (new Branch)->keyToLabel(Branch::LOCATION_PENANG),
            ];
            $links = [
                Branch::LOCATION_KL => route('customer.create_link', ['branch' => Crypt::encrypt(Branch::LOCATION_KL)]),
                Branch::LOCATION_PENANG => route('customer.create_link', ['branch' => Crypt::encrypt(Branch::LOCATION_PENANG)]),
            ];

            $view->with('branches', $branches);
            $view->with('links', $links);
        });
        View::composer(['user_management.form'], function (ViewView $view) {
            $branches = [
                Branch::LOCATION_KL => (new Branch)->keyToLabel(Branch::LOCATION_KL),
                Branch::LOCATION_PENANG => (new Branch)->keyToLabel(Branch::LOCATION_PENANG),
            ];
            $sales_agent_ids = DB::table('sales_sales_agents')->pluck('sales_agent_id')->toArray();
            $sales_agents = SalesAgent::whereNotIn('id', $sales_agent_ids)->orderBy('name')->get();

            $view->with('branches', $branches);
            $view->with('sales_agents', $sales_agents);
        });
        View::composer(['components.app.modal.transfer-modal'], function (ViewView $view) {
            $branches = [
                Branch::LOCATION_KL => (new Branch)->keyToLabel(Branch::LOCATION_KL),
                Branch::LOCATION_PENANG => (new Branch)->keyToLabel(Branch::LOCATION_PENANG),
            ];
            unset($branches[isSuperAdmin() ? Session::get('as_branch') : Auth::user()->branch->location]);

            $view->with('branches', $branches);
        });
        View::composer(['quotation.form_step.quotation_details', 'quotation.convert', 'sale_order.form_step.quotation_details'], function (ViewView $view) {
            $report_types = ProjectType::orderBy('id', 'desc')->get();

            $view->with('report_types', $report_types);
        });
        View::composer(['promotion.form', 'grn.form'], function (ViewView $view) {
            $products = Product::where('type', Product::TYPE_RAW_MATERIAL)->orderBy('id', 'desc')->get();

            $view->with('products', $products);
        });
        View::composer(['grn.form'], function (ViewView $view) {
            $uoms = UOM::where('is_active', true)->orderBy('id', 'desc')->get();
            $suppliers = Supplier::where('is_active', true)->orderBy('id', 'desc')->get();
            $credit_terms = CreditTerm::where('is_active', true)->orderBy('id', 'desc')->get();

            $view->with('suppliers', $suppliers);
            $view->with('uoms', $uoms);
            $view->with('credit_terms', $credit_terms);
        });
        View::composer(['supplier.form', 'customer.form_step.info'], function (ViewView $view) {
            $currencies = Currency::where('is_active', true)->orderBy('id', 'desc')->get();
            $credit_terms = CreditTerm::where('is_active', true)->orderBy('id', 'desc')->get();
            $areas = Area::where('is_active', true)->orderBy('id', 'desc')->get();
            $debtor_types = DebtorType::where('is_active', true)->orderBy('id', 'desc')->get();

            $view->with('currencies', $currencies);
            $view->with('credit_terms', $credit_terms);
            $view->with('areas', $areas);
            $view->with('debtor_types', $debtor_types);
        });
        View::composer(['customer.list', 'supplier.list', 'dealer.list'], function (ViewView $view) {
            $debtor_types = DebtorType::where('is_active', true)->orderBy('id', 'desc')->get();
            $company_group = [
                1 => 'Power Cool',
                2 => 'Hi-Ten',
            ];
            if (isSalesOnly()) {
                $sales_agents_ids = DB::table('sales_sales_agents')->where('sales_id', Auth::user()->id)->pluck('sales_agent_id')->toArray();

                $sales_agents = SalesAgent::whereIn('id', $sales_agents_ids)->orderBy('name', 'asc')->get();
            } else {
                $sales_agents = SalesAgent::orderBy('name', 'asc')->get();
            }

            $view->with('debtor_types', $debtor_types);
            $view->with('company_group', $company_group);
            $view->with('business_types', Customer::BUSINESS_TYPES);
            $view->with('sales_agents', $sales_agents);
        });
        View::composer(['dealer.form'], function (ViewView $view) {
            $company_group = [
                1 => 'Power Cool',
                2 => 'Hi-Ten',
            ];

            $view->with('company_group', $company_group);
        });
        View::composer(['customer.form_step.info'], function (ViewView $view) {
            $platforms = Platform::where('is_active', true)->orderBy('id', 'desc')->get();

            if (isSalesOnly()) {
                $sales_agents_ids = DB::table('sales_sales_agents')->where('sales_id', Auth::user()->id)->pluck('sales_agent_id')->toArray();
                $sales_agents = SalesAgent::whereIn('id', $sales_agents_ids)->orderBy('name', 'asc')->get();
            } else {
                $sales_agents = SalesAgent::orderBy('name', 'asc')->get();
            }

            $view->with('platforms', $platforms);
            $view->with('is_create_link', isCreateLink());
            $view->with('sales_agents', $sales_agents);
        });
        View::composer(['customer.form_step.info', 'supplier.form'], function (ViewView $view) {
            $msics = MsicCode::all();

            $view->with('msics', $msics);
        });
        View::composer(['billing.convert'], function (ViewView $view) {
            $sales = User::whereHas('roles', function ($q) {
                $q->where('id', Role::SALE);
            })->orderBy('id', 'desc')->get();
            $terms = CreditTerm::where('is_active', true)->orderBy('id', 'desc')->get();
            $costs = ProductCost::orderBy('id', 'desc')->get();

            $view->with('sales', $sales);
            $view->with('terms', $terms);
            $view->with('costs', $costs);
        });
        View::composer(['service_reminder.form'], function (ViewView $view) {
            $sale_orders = Sale::with('products.children.productChild')->where('type', Sale::TYPE_SO)->orderBy('id', 'desc')->get();

            $view->with([
                'sale_orders' => $sale_orders,
            ]);
        });
        View::composer(['sale_order.form_step.payment_details', 'quotation.form_step.quotation_details'], function (ViewView $view) {
            $payment_statuses = [
                Sale::PAYMENT_STATUS_UNPAID => 'Unpaid',
                Sale::PAYMENT_STATUS_PARTIALLY_PAID => 'Partially Paid',
                Sale::PAYMENT_STATUS_PAID => 'Paid',
            ];
            $payment_methods = PaymentMethod::orderBy('name', 'asc')->get();
            $credit_terms = CreditTerm::orderBy('id', 'desc')->get();
            $credit_term_payment_method_ids = getPaymentMethodCreditTermIds();

            $view->with([
                'can_payment_amount' => in_array(Role::SUPERADMIN, getUserRoleId(Auth::user())) || in_array(Role::FINANCE, getUserRoleId(Auth::user())),
                'payment_statuses' => $payment_statuses,
                'payment_methods' => $payment_methods,
                'credit_payment_method_ids' => $credit_term_payment_method_ids,
                'credit_terms' => $credit_terms,
            ]);
        });
        View::composer(['delivery_order.generate_transport_acknowledgement'], function (ViewView $view) {
            $delivery_orders = DeliveryOrder::whereNull('transport_ack_filename')->orderBy('id', 'desc')->get();
            $dealers = Dealer::orderBy('id', 'desc')->get();
            $types = [
                DeliveryOrder::TRANSPORT_ACK_TYPE_DELIVERY => 'Delivery',
                DeliveryOrder::TRANSPORT_ACK_TYPE_COLLECTION => 'Collection',
            ];

            $view->with([
                'delivery_orders' => $delivery_orders,
                'dealers' => $dealers,
                'types' => $types,
            ]);
        });
        View::composer(['transport_ack.generate'], function (ViewView $view) {
            $products = Product::with('children')->orderBy('id', 'desc')->get();
            $dealers = Dealer::orderBy('id', 'desc')->get();
            $types = [
                DeliveryOrder::TRANSPORT_ACK_TYPE_DELIVERY => 'Delivery',
                DeliveryOrder::TRANSPORT_ACK_TYPE_COLLECTION => 'Collection',
            ];

            $view->with([
                'products' => $products,
                'dealers' => $dealers,
                'types' => $types,
            ]);
        });
        View::composer(['customer.form_step.info', 'supplier.form', 'grn.form', 'delivery_order.convert_to_invoice', 'inventory.form', 'uom.form', 'inventory_category.form', 'inventory_type.form'], function (ViewView $view) {
            $company_group = [
                1 => 'Power Cool',
                2 => 'Hi-Ten',
            ];

            $view->with([
                'company_group' => $company_group,
            ]);
        });
        View::composer(['inventory_category.form'], function (ViewView $view) {
            $factories = Factory::orderBy('id', 'desc')->get();

            $view->with([
                'factories' => $factories,
            ]);
        });
        View::composer(['customer.form_step.info', 'supplier.form'], function (ViewView $view) {
            $business_types = [
                1 => 'Business',
                2 => 'Individual',
                3 => 'Government',
            ];

            $view->with([
                'business_types' => $business_types,
            ]);
        });
        View::composer(['inventory_type.form'], function (ViewView $view) {
            $inventory_types = [
                InventoryType::TYPE_PRODUCT => 'Product',
                InventoryType::TYPE_RAW_MATERIAL => 'Raw Material / Sparepart',
            ];

            $view->with([
                'inventory_types' => $inventory_types,
            ]);
        });
        View::composer(['vehicle_service.form'], function (ViewView $view) {
            $vehicles = Vehicle::orderBy('id', 'desc')->get();
            $services = VehicleService::types;

            $view->with([
                'vehicles' => $vehicles,
                'services' => $services,
            ]);
        });
        View::composer(['milestone.form'], function (ViewView $view) {
            $categories = InventoryCategory::orderBy('name', 'asc')->get();
            $types = InventoryType::orderBy('name', 'asc')->get();
            $milestones = Milestone::where('type', Milestone::TYPE_PRODUCTION)->whereNotNull('batch')->get();

            $view->with([
                'categories' => $categories,
                'types' => $types,
                'existing_milestones' => $milestones,
            ]);
        });
        View::composer(['production_request.form', 'raw_material_request.form'], function (ViewView $view) {
            if (str_contains(Route::currentRouteName(), 'raw_material_request.')) {
                $products = Product::where('type', Product::TYPE_RAW_MATERIAL)->orderBy('model_name', 'asc')->get();
            } else {
                $products = Product::where('type', Product::TYPE_PRODUCT)->orderBy('model_name', 'asc')->get();
            }

            $view->with([
                'products' => $products,
            ]);
        });
        View::composer(['raw_material_request.form'], function (ViewView $view) {
            $productions = Production::orderBy('id', 'desc')->get();

            $view->with([
                'productions' => $productions,
            ]);
        });
        View::composer(['components.app.modal.production-request-complete-modal'], function (ViewView $view) {
            $production_id_to_exclude = ProductionRequestMaterial::whereNotNull('production_id')->pluck('production_id')->toArray();
            $productions = Production::whereNotIn('id', $production_id_to_exclude)->orderBy('id', 'desc')->get();

            $view->with([
                'productions' => $productions,
            ]);
        });
        View::composer(['components.app.modal.to-material-use-modal'], function (ViewView $view) {
            $bom_product_ids = MaterialUse::pluck('product_id');
            $products = Product::whereIn('id', $bom_product_ids)->orderBy('id', 'desc')->get();

            $view->with([
                'products' => $products,
            ]);
        });
    }
}
