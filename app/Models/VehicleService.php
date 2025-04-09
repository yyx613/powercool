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

    public function getInsuranceDateAttribute($val)
    {
        return $val == null ? null : Carbon::parse($val)->format('Y-m-d');
    }

    public function getInsuranceRemindAtAttribute($val)
    {
        return $val == null ? null : Carbon::parse($val)->format('Y-m-d');
    }

    public function getRoadtaxDateAttribute($val)
    {
        return $val == null ? null : Carbon::parse($val)->format('Y-m-d');
    }

    public function getRoadtaxRemindAtAttribute($val)
    {
        return $val == null ? null : Carbon::parse($val)->format('Y-m-d');
    }

    public function getInspectionDateAttribute($val)
    {
        return $val == null ? null : Carbon::parse($val)->format('Y-m-d');
    }

    public function getInspectionRemindAtAttribute($val)
    {
        return $val == null ? null : Carbon::parse($val)->format('Y-m-d');
    }

    public function getMileageRemindAtAttribute($val)
    {
        return $val == null ? null : Carbon::parse($val)->format('Y-m-d');
    }
}
