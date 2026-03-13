<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Branch;
use App\Models\Role;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (isSuperAdmin()) {
            Session::put('as_branch', Branch::LOCATION_KL);
        }
        if (isSalesOnly()) {
            return redirect()->intended('/quotation');
        }

        // Redirect to first accessible page (sidebar menu order)
        $user = $request->user();
        $redirectMap = [
            'notification.view' => '/notification',
            'approval.view' => '/approval',
            'dashboard.view' => '/dashboard',
            'customer.view' => '/customer',
            'sale.quotation.view' => '/quotation',
            'sale.sale_order.view' => '/sale-order',
            'sale.delivery_order.view' => '/delivery-order',
            'sale.invoice.view' => '/invoice',
            'inventory.product.view' => '/inventory/product',
            'production.view' => '/production',
            'ticket.view' => '/ticket',
            'report.view' => '/report',
        ];

        foreach ($redirectMap as $permission => $route) {
            if ($user->can($permission)) {
                return redirect()->intended($route);
            }
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
