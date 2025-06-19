<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([BranchScope::class])]
class MaterialUse extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function approval()
    {
        return $this->morphOne(Approval::class, 'object');
    }

    public function materials()
    {
        return $this->hasMany(MaterialUseProduct::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->morphOne(Branch::class, 'object');
    }

    public function avgCost()
    {
        $cost = 0;
        for ($i = 0; $i < count($this->materials); $i++) {
            if ($this->materials[$i]->status == MaterialUseProduct::STATUS_DISABLED) {
                continue;
            }
            $cost += $this->materials[$i]->material->avgCost() * $this->materials[$i]->qty;
        }
        return $cost;
    }
}
