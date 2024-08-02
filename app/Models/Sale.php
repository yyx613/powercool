<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_QUO = 1; // QUOTATION id
    const TYPE_SO = 2; // SALE ORDER id

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function products() {
        return $this->hasMany(SaleProduct::class);
    }

    public function generateSku($type): null|string {
        $sku = null;
        
        while (true) {
            $sku = ($type == self::TYPE_QUO ? 'Q' : 'SO') . now()->format('ym') . generateRandomAlphabet();
            $exists = self::where(DB::raw('BINARY `sku`'), $sku)->exists();

            if (!$exists) {
                break;
            }
        }

        return $sku;
    }
}
