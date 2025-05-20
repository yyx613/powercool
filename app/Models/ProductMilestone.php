<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductMilestone extends Model
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
    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getMaterialUseProductIdAttribute($val)
    {
        return json_decode($val);
    }
}
