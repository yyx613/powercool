<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjectCreditTerm extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'under_warranty' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function creditTerm() {
        return $this->belongsTo(CreditTerm::class);
    }
}
