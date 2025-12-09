<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for super admins or users without a branch
        if (isSuperAdmin() || getCurrentUserBranch() == null) {
            $asBranch = Session::get('as_branch');

            // If branch is not selected or is "Every"
            if ($asBranch === null || (int) $asBranch === Branch::LOCATION_EVERY) {
                // Store intended URL for redirect after selection
                Session::put('branch_redirect_url', $request->fullUrl());

                return redirect()->route('branch.select');
            }
        }

        return $next($request);
    }
}
