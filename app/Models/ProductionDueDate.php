<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionDueDate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:d M Y H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function doneBy()
    {
        return $this->belongsTo(User::class, 'done_by')->withoutGlobalScope(BranchScope::class);
    }

    public function getOldDateAttribute($val)
    {
        return Carbon::parse($val)->format('Y M d');
    }

    public function getNewDateAttribute($val)
    {
        return Carbon::parse($val)->format('Y M d');
    }

    public function getCreatedAtAttribute($val)
    {
        return Carbon::parse($val)->format('d M Y, H:i');
    }
}
