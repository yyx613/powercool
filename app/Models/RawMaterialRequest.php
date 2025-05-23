<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([BranchScope::class])]
class RawMaterialRequest extends Model
{
    use HasFactory;

    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETED = 2;

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

    public function materials()
    {
        return $this->hasMany(RawMaterialRequestMaterial::class);
    }

    public function completedMaterials()
    {
        return $this->materials->where('status', RawMaterialRequestMaterial::MATERIAL_STATUS_COMPLETED);
    }
}
