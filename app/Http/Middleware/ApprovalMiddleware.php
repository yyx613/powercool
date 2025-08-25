<?php

namespace App\Http\Middleware;

use App\Models\Approval;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApprovalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $pending_approval_count = Approval::where('status', Approval::STATUS_PENDING_APPROVAL)->count();
            Cache::put('unread_approval_count', $pending_approval_count);
        } catch (\Throwable $th) {
            Cache::put('unread_approval_count', 0);
        }
        return $next($request);
    }
}
