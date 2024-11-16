<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class NotificationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $unread_notis = $request->user()->unreadNotifications;
            Session::put('unread_noti_count', count($unread_notis));
        } catch (\Throwable $th) {
            Session::put('unread_noti_count', 0);
        }

        return $next($request);
    }
}
