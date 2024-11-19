<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskMilestone extends Pivot 
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'submitted_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function task() {
        return $this->belongsTo(Task::class);
    }

    public function inventories() {
        return $this->hasMany(TaskMilestoneInventory::class, 'task_milestone_id');
    }

    public function attachments() {
        return $this->morphMany(Attachment::class, 'object');
    }
}
