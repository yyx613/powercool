<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([BranchScope::class])]
class MsicCode extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function customer()
    {
        return $this->hasOne(Customer::class, 'msic_id');
    }

    public function branch() {
        return $this->morphOne(Branch::class, 'object');
    }
}
