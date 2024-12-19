<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsicCode extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function customer()
    {
        return $this->hasOne(Customer::class, 'msic_id');
    }
}
