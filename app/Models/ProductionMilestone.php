<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductionMilestone extends Pivot
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

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function materials()
    {
        return $this->hasMany(ProductionMilestoneMaterial::class);
    }

    public function materialsPreview()
    {
        return $this->hasMany(ProductionMilestoneMaterialPreview::class);
    }

    public function rejects()
    {
        return $this->belongsTo(ProductionMilestoneReject::class);
    }
}
