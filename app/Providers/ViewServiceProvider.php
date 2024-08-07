<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Milestone;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewView;

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
        View::composer(['task.form'], function(ViewView $view) {
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
        View::composer(['task.form'], function(ViewView $view) {
            $is_edit = false;
            if (str_contains(Route::currentRouteName(), '.edit')) {
                $is_edit = true;
            }

            if (str_contains(Route::currentRouteName(), '.technician.')) {
                $users = User::whereHas('roles', function($q) {
                    $q->where('id', Role::TECHNICIAN);
                })->orderBy('id', 'desc')->get();
                $milestones = Milestone::where(function($q) {
                    $q->where('type', Milestone::TYPE_SERVICE_TASK)->orWhere('type', Milestone::TYPE_INSTALLER_TASK);
                });
                
                $form_route_name = 'task.technician.store';
                if ($is_edit) {
                    $form_route_name = 'task.technician.update';
                } else {
                    $milestones->where('is_custom', false);
                }
            } else if (str_contains(Route::currentRouteName(), '.sale.')) {
                $users = User::whereHas('roles', function($q) {
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
                $users = User::whereHas('roles', function($q) {
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
            $customers = Customer::orderBy('id', 'desc')->get();

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
        View::composer(['ticket.form', 'quotation.form_step.quotation_details', 'sale_order.form_step.quotation_details'], function(ViewView $view) {
            $customers = Customer::orderBy('id', 'desc')->get();

            $view->with('customers', $customers);
        });
        View::composer(['quotation.form_step.quotation_details', 'sale_order.form_step.quotation_details'], function(ViewView $view) {
            $sales = User::whereHas('roles', function($q) {
                $q->where('id', Role::SALE);
            })->orderBy('id', 'desc')->get();

            $view->with('sales', $sales);
        });
        View::composer(['sale_order.form_step.delivery_schedule'], function(ViewView $view) {
            $drivers = User::whereHas('roles', function($q) {
                $q->where('id', Role::DRIVER);
            })->orderBy('id', 'desc')->get();

            $view->with('drivers', $drivers);
        });
        View::composer(['customer.form', 'quotation.form_step.customer_details'], function(ViewView $view) {
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
        View::composer(['user_management.form', 'components.app.modal.convert-ticket-modal'], function(ViewView $view) {
            $roles = Role::whereNot('id', Role::SUPERADMIN)->get();

            $view->with([
                'roles' => $roles
            ]);
        });
    }
}
