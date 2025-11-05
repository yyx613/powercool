<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([BranchScope::class])]
class VehicleService extends Model
{
    use HasFactory, SoftDeletes;

    const types = [
        1 => 'Insurance',
        2 => 'Roadtax',
        3 => 'Inspection',
        4 => 'Mileage',
        5 => 'Repair Item',
        6 => 'Service Item',
        7 => 'Petrol',
        8 => 'Toll',
    ];

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

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function items()
    {
        return $this->hasMany(VehicleServiceItem::class);
    }

    public function getDateAttribute($val)
    {
        return $val == null ? null : Carbon::parse($val)->format('Y-m-d');
    }

    public function getToDateAttribute($val)
    {
        return $val == null ? null : Carbon::parse($val)->format('Y-m-d');
    }

    public function getRemindAtAttribute($val)
    {
        return $val == null ? null : Carbon::parse($val)->format('Y-m-d');
    }
}
