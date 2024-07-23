<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_DRIVER = 1; 
    const TYPE_TECHNICIAN = 2; 
    const TYPE_SALE = 3; 

    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;

    const STATUS_TO_DO = 1;
    const STATUS_DOING = 2;
    const STATUS_IN_REVIEW = 3;
    const STATUS_COMPLETED = 4;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_task', 'task_id', 'user_id');
    }

    public function milestones() {
        return $this->belongsToMany(Milestone::class, 'task_milestone', 'task_id', 'milestone_id');
    }

    public function attachments() {
        return $this->morphMany(Attachment::class, 'object');
    }
}
