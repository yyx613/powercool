<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashSaleLocation extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_BILLING = 1;
    const TYPE_DELIVERY = 2;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function formatAddress()
    {
        $addr = null;

        if ($this->address1 != null) {
            $addr .= $this->address1 . '<br>';
        }
        if ($this->address2 != null) {
            $addr .= $this->address2 . '<br>';
        }
        if ($this->address3 != null) {
            $addr .= $this->address3 . '<br>';
        }
        if ($this->address4 != null) {
            $addr .= $this->address4 . '<br>';
        }

        return $addr;
    }
}
