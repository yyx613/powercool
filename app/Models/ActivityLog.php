<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

#[ScopedBy([BranchScope::class])]
class ActivityLog extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function branch() {
        return $this->morphOne(Branch::class, 'object');
    }

    public function doneBy() {
        return $this->belongsTo(User::class, 'done_by');
    }

    public function store($class, $class_id, $desc, $data=null) {
        $data = self::create([
            'object_type' => $class,
            'object_id' => $class_id,
            'desc' => $desc,
            'data' => $data,
            'done_by' => Auth::user()->id,
        ]);
        (new Branch)->assign(ActivityLog::class, $data->id);
    }
}
