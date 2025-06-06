<?php

namespace App\Models\Scopes;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BranchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::hasUser()) {
            $user_branch = Auth::user()->branch;

            if (
                (isSuperAdmin() && Session::get('as_branch') != Branch::LOCATION_EVERY) ||
                $user_branch != null
            ) {
                $builder->whereHas('branch', function ($q) use ($user_branch) {
                    $q->where('location', isSuperAdmin() ? Session::get('as_branch') : $user_branch->location);
                });
            }
        }
    }
}
