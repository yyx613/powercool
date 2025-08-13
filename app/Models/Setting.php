<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    const SST_KEY = 'sst';
    const TAX_CODE_KEY = 'tax_code';

    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'amount' => 'float',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }
}
