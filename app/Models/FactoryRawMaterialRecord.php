<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactoryRawMaterialRecord extends Model
{
    use HasFactory;

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

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function uomObj()
    {
        return $this->belongsTo(UOM::class, 'uom');
    }

    public function doneBy()
    {
        return $this->belongsTo(User::class, 'done_by')->withoutGlobalScope(BranchScope::class);
    }
}
