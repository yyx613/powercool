<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([BranchScope::class])]
class ClassificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'classification_code_product');
    }

    public function branch() {
        return $this->morphOne(Branch::class, 'object');
    }
}
