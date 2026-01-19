<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

#[ScopedBy([BranchScope::class])]
class AdhocService extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function branch()
    {
        return $this->morphOne(Branch::class, 'object');
    }

    public function saleAdhocServices()
    {
        return $this->hasMany(SaleAdhocService::class);
    }

    public function generateSku(?bool $is_hi_ten = null): string
    {
        $existing_skus = self::withoutGlobalScope(BranchScope::class)->pluck('sku')->toArray();

        return generateSku('ADSVC', $existing_skus, $is_hi_ten);
    }
}
