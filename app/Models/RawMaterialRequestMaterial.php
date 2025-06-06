<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialRequestMaterial extends Model
{
    use HasFactory;

    const MATERIAL_STATUS_IN_PROGRESS = 1;
    const MATERIAL_STATUS_COMPLETED = 2;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function materialRequest()
    {
        return $this->belongsTo(RawMaterialRequest::class, 'raw_material_request_id');
    }

    public function materialCollected()
    {
        return $this->hasMany(RawMaterialRequestMaterialCollected::class);
    }

    public function material()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
