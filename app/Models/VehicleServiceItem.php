<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleServiceItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'warranty_expiry_date' => 'date',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function getWarrantyExpiryDateAttribute($val)
    {
        return $val == null ? null : \Carbon\Carbon::parse($val)->format('Y-m-d');
    }
}
