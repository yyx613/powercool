<?php

namespace App\Models\Scopes;

use App\Models\Approval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ApprovedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->WhereHas('approval', function ($q) {
            $q->where('status', Approval::STATUS_APPROVED);
        })
            ->orDoesntHave('approval');
    }
}
