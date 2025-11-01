<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([BranchScope::class])]
class FactoryRawMaterial extends Model
{
    use HasFactory;

    const STATUS_IN_TRANSIT = 1;
    const STATUS_REJECTED = 2;
    const STATUS_ACCEPTED = 3;
    const STATUS_APPROVAL_REJECTED = 4;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function branch()
    {
        return $this->morphOne(Branch::class, 'object');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function records()
    {
        return $this->hasMany(FactoryRawMaterialRecord::class);
    }

    public function remainingQty()
    {
        return $this->qty - ($this->to_warehouse_qty ?? 0) - $this->records->sum('qty');
    }
}
