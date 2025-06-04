<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialRequestMaterialCollected extends Model
{
    use HasFactory;

    protected $table = 'raw_material_request_material_collected';
    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function loggedBy()
    {
        return $this->belongsTo(User::class, 'logged_by')->withoutGlobalScope(BranchScope::class);
    }
}
