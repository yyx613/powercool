<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionMilestoneMaterialPreview extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'production_milestone_materials_preview';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function productionMilestone()
    {
        return $this->belongsTo(ProductionMilestone::class);
    }
}
