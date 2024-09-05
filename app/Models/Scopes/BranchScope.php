<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BranchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::hasUser()) {
            $user_branch = Auth::user()->branch;
    
            if ($user_branch != null) {
                $builder->whereHas('branch', function($q) use ($user_branch) {
                    $q->where('location', $user_branch->location);
                });
            }
        }
    }
}
