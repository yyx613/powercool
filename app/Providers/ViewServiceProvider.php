<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Milestone;
use App\Models\Role;
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
        View::composer(['task.list', 'task.form'], function(ViewView $view) {
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
                $milestones = Milestone::where('type', Milestone::TYPE_SERVICE_TASK)->orWhere('type', Milestone::TYPE_INSTALLER_TASK)->get();
                $form_route_name = 'task.technician.store';
                if ($is_edit) {
                    $form_route_name = 'task.technician.update';
                }
            } else if (str_contains(Route::currentRouteName(), '.sale.')) {
                $milestones = Milestone::where('type', Milestone::TYPE_SITE_VISIT)->get();
                $form_route_name = 'task.sale.store';
                if ($is_edit) {
                    $form_route_name = 'task.sale.update';
                }
            } else if (str_contains(Route::currentRouteName(), '.driver.')) {
                $milestones = Milestone::where('type', Milestone::TYPE_DRIVER_TASK)->get();
                $form_route_name = 'task.driver.store';
                if ($is_edit) {
                    $form_route_name = 'task.driver.update';
                }
            } 

            $customers = Customer::orderBy('id', 'desc')->get();

            $users = User::whereHas('roles', function($q) {
                $q->whereIn('id', [Role::DRIVER, Role::TECHNICIAN, Role::SALE]);
            })->orderBy('id', 'desc')->get();


            if (str_contains(Route::currentRouteName(), '.technician.')) {
                $view->with('task_types', [
                    Milestone::TYPE_SERVICE_TASK => 'Service',
                    Milestone::TYPE_INSTALLER_TASK => 'Installer',
                ]);
            }

            $view->with([
                'form_route_name' => $form_route_name,
                'milestones' => $milestones,
                'users' => $users,
                'customers' => $customers,
            ]);
        });

        View::composer(['customer.form'], function(ViewView $view) {
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
    }
}
