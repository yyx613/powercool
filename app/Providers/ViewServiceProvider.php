<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\InventoryCategory;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\ProductionMilestoneMaterial;
use App\Models\Report;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use App\Models\Supplier;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WarrantyPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
                'inventory.summary' => [],
                'inventory.category' => [],
                'inventory.product' => [],
                'inventory.raw_material' => [],
                'sale.quotation' => [],
                'sale.sale_order' => [],
                'sale.delivery_order' => [],
                'sale.invoice' => [],
                'sale.target' => [],
                'task' => [],
                'production' => [],
                'ticket' => [],
                'customer' => [],
                'supplier' => [],
                'setting' => [],
            ];

            for ($i = 0; $i < count($permissions); $i++) {
                if (str_contains($permissions[$i], 'inventory.summary')) {
                    array_push($permissions_group['inventory.summary'], $permissions[$i]);
                } else if (str_contains($permissions[$i], 'inventory.category')) {
                    array_push($permissions_group['inventory.category'], $permissions[$i]);
                } else if (str_contains($permissions[$i], 'inventory.product')) {
                    array_push($permissions_group['inventory.product'], $permissions[$i]);
                } else if (str_contains($permissions[$i], 'inventory.raw_material')) {
                    array_push($permissions_group['inventory.raw_material'], $permissions[$i]);
                } else if (str_contains($permissions[$i], 'sale.quotation')) {
                    array_push($permissions_group['sale.quotation'], $permissions[$i]);
                } else if (str_contains($permissions[$i], 'sale.sale_order')) {
                    array_push($permissions_group['sale.sale_order'], $permissions[$i]);
                } else if (str_contains($permissions[$i], 'sale.delivery_order')) {
                    array_push($permissions_group['sale.delivery_order'], $permissions[$i]);
                } else if (str_contains($permissions[$i], 'sale.invoice')) {
                    array_push($permissions_group['sale.invoice'], $permissions[$i]);
                } else if (str_contains($permissions[$i], 'sale.target')) {
                    array_push($permissions_group['sale.target'], $permissions[$i]);
                } else if (str_contains($permissions[$i], 'task')) {
                    array_push($permissions_group['task'], $permissions[$i]);
                } else if (str_contains($permissions[$i], 'production')) {
                    array_push($permissions_group['production'], $permissions[$i]);
                } else if (str_contains($permissions[$i], 'ticket')) {
                    array_push($permissions_group['ticket'], $permissions[$i]);
                } else if (str_contains($permissions[$i], 'customer')) {
                    array_push($permissions_group['customer'], $permissions[$i]);
                } else if (str_contains($permissions[$i], 'supplier')) {
                    array_push($permissions_group['supplier'], $permissions[$i]);
                } else if (str_contains($permissions[$i], 'setting')) {
                    array_push($permissions_group['setting'], $permissions[$i]);
                }
            }

            $view->with('permissions_group', $permissions_group);
        });
        View::composer(['task.form'], function (ViewView $view) {
            if (str_contains(Route::currentRouteName(), '.technician.')) {
                $for_role = 'technician';
            } else if (str_contains(Route::currentRouteName(), '.sale.')) {
                $for_role = 'sale';
            } else if (str_contains(Route::currentRouteName(), '.driver.')) {
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
            } else if (str_contains(Route::currentRouteName(), '.sale.')) {
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
            } else if (str_contains(Route::currentRouteName(), '.driver.')) {
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
                $customers = Customer::orderBy('id', 'desc')->where('is_active', true)->get();
            }

            // Return data
            if (str_contains(Route::currentRouteName(), '.technician.')) {
                $view->with('task_types', [
                    Milestone::TYPE_SERVICE_TASK => 'Service',
                    Milestone::TYPE_INSTALLER_TASK => 'Installer',
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
        View::composer(['ticket.form', 'quotation.form_step.quotation_details', 'sale_order.form_step.quotation_details'], function (ViewView $view) {
            $is_edit = false;
            if (str_contains(Route::currentRouteName(), '.edit')) {
                $is_edit = true;
            }
            if ($is_edit) {
                $customers = Customer::orderBy('id', 'desc')->get();
            } else {
                $customers = Customer::orderBy('id', 'desc')->where('is_active', true)->get();
            }

            $view->with('customers', $customers);
        });
        View::composer(['quotation.form_step.quotation_details', 'sale_order.form_step.quotation_details', 'target.form'], function (ViewView $view) {
            $sales = User::whereHas('roles', function ($q) {
                $q->where('id', Role::SALE);
            })->orderBy('id', 'desc')->get();

            $view->with('sales', $sales);
        });
        View::composer(['sale_order.form_step.delivery_schedule', 'components.app.modal.transfer-modal'], function (ViewView $view) {
            $drivers = User::whereHas('roles', function ($q) {
                $q->where('id', Role::DRIVER);
            })->orderBy('id', 'desc')->get();

            $view->with('drivers', $drivers);
        });
        View::composer(['customer.form', 'supplier.form', 'quotation.form_step.customer_details'], function (ViewView $view) {
            $prefix = [
                'mr' => 'Mr',
                'mrs' => 'Mrs',
                'ms' => 'Ms',
                'miss' => 'Miss',
            ];

            $view->with([
                'prefix' => $prefix
            ]);
        });
        View::composer(['user_management.form', 'components.app.modal.convert-ticket-modal'], function (ViewView $view) {
            $roles = Role::whereNot('id', Role::SUPERADMIN)->get();

            $view->with([
                'roles' => $roles
            ]);
        });
        View::composer(['quotation.form_step.product_details', 'sale_order.form_step.product_details'], function (ViewView $view) {
            // Products
            $exclude_ids = [];
            // Not in production
            $pmm_ids = ProductionMilestoneMaterial::pluck('product_child_id')->toArray();
            // Exclude converted sale
            $sale_ids = Sale::where('status', Sale::STATUS_CONVERTED)->pluck('id');
            $converted_sp_ids = SaleProduct::whereIn('sale_id', $sale_ids)->pluck('id');
            // Exclude current sale, if edit
            if (str_contains(Route::currentRouteName(), '.edit')) {
                $sale = request()->route()->parameter('sale');
                $sp_ids = $sale->products()->pluck('id')->toArray();
                $exclude_ids = SaleProductChild::whereIn('sale_product_id', $sp_ids)->pluck('product_children_id')->toArray();
            }

            $assigned_pc_ids = SaleProductChild::distinct()
                ->whereNotIn('sale_product_id', $converted_sp_ids)
                ->whereNotIn('product_children_id', $exclude_ids)
                ->pluck('product_children_id')
                ->toArray();

            $products = Product::with(['children' => function ($q) use ($assigned_pc_ids, $pmm_ids) {
                $q->whereNull('status')->whereNotIn('id', $assigned_pc_ids)->whereNotIn('id', $pmm_ids);
            }])
                ->withCount(['children' => function ($q) use ($assigned_pc_ids, $pmm_ids) {
                    $q->whereNull('status')->whereNotIn('id', $assigned_pc_ids)->whereNotIn('id', $pmm_ids);
                }])
                ->where('is_active', true)
                ->where('type', Product::TYPE_PRODUCT)
                ->orWhere(function ($q) {
                    $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', true);
                })
                ->having('children_count', '>', 0)
                ->orderBy('id', 'desc')
                ->get();

            // Warranty Periods
            $wps = WarrantyPeriod::where('is_active', true)->orderBy('id', 'desc')->get();

            $view->with([
                'products' => $products,
                'warranty_periods' => $wps,
            ]);
        });
        View::composer(['inventory.form'], function (ViewView $view) {
            $suppliers = Supplier::where('is_active', true)->orderBy('id', 'desc')->get();
            $inv_cats = InventoryCategory::where('is_active', true)->orderBy('id', 'desc')->get();

            $view->with([
                'inv_cats' => $inv_cats,
                'suppliers' => $suppliers,
            ]);
        });
        View::composer(['inventory.list', 'inventory.form', 'inventory.view', 'components.app.modal.stock-in-modal', 'components.app.modal.stock-out-modal'], function (ViewView $view) {
            $is_product = true;
            if (str_contains(Route::currentRouteName(), 'raw_material.')) {
                $is_product = false;
            }

            $view->with([
                'is_product' => $is_product,
            ]);
        });
        View::composer(['production.form'], function (ViewView $view) {
            $users = User::whereHas('roles', function ($q) {
                $q->where('id', Role::SALE);
            })->orderBy('id', 'desc')->get();

            $milestones = Milestone::where('type', Milestone::TYPE_PRODUCTION)->orderBy('id', 'desc')->get();

            $products = Product::where('type', Product::TYPE_PRODUCT)
                ->orWhere(function ($q) {
                    $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', true);
                })
                ->withCount('materialUse')
                ->having('material_use_count', '>', 0)
                ->orderBy('id', 'desc')
                ->get();

            $sales = Sale::orderBy('id', 'desc')->get();

            $view->with('users', $users);
            $view->with('milestones', $milestones);
            $view->with('products', $products);
            $view->with('sales', $sales);
        });
        View::composer(['material_use.form'], function (ViewView $view) {
            $products = Product::where('type', Product::TYPE_PRODUCT)->orWhere(function ($q) {
                $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', true);
            })->orderBy('id', 'desc')->get();
            $materials = Product::where('type', Product::TYPE_RAW_MATERIAL)->orderBy('id', 'desc')->get();

            $view->with('products', $products);
            $view->with('materials', $materials);
        });
        View::composer(['layouts.superadmin_nav', 'user_management.form'], function (ViewView $view) {
            $branches = [
                1 => 'Kuala Lumpur',
                2 => 'Penang',
            ];

            $view->with('branches', $branches);
        });
        View::composer(['components.app.modal.transfer-modal'], function (ViewView $view) {
            $branches = [
                1 => 'Kuala Lumpur',
                2 => 'Penang',
            ];
            unset($branches[isSuperAdmin() ? Session::get('as_branch') : Auth::user()->branch->location]);

            $view->with('branches', $branches);
        });
        View::composer(['quotation.form_step.quotation_details', 'quotation.convert', 'sale_order.form_step.quotation_details'], function (ViewView $view) {
            $report_types = [
                Report::TYPE_EITHER => 'Either',
                Report::TYPE_NORMAL => 'Normal',
                Report::TYPE_PROJECT => 'Project',
                Report::TYPE_FRANCHISE => 'Franchise',
            ];

            $view->with('report_types', $report_types);
        });
    }
}
