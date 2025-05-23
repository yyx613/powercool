<?php

namespace App\Http\Middleware;

use App\Models\Production;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class ProductionWorkerCanAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (isProductionWorker()) {
            if (Route::currentRouteName() == 'production.view') {
                $production = $request->route('production');
                if ($production->status != Production::STATUS_DOING) {
                    return redirect(route('production.index'))->with('warning', 'Unauthorized');
                }
            }
        }
        return $next($request);
    }
}
