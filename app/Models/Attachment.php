<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;

    const TASK_PATH = 'public/attachments/task';
    const CUSTOMER_PATH = 'public/attachments/customer';

    protected $guarded = [];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
    protected $appends = ['url'];

    public function getUrlAttribute() {
        $path = 'public/storage';
        if (config('app.env') == 'local') {
            $path = 'storage';
        }

        switch ($this->object_type) {
            case Task::class:
                return config('app.url') . str_replace('public', $path, self::TASK_PATH) . '/' . $this->src;
            case Customer::class:
                return config('app.url') . str_replace('public', $path, self::CUSTOMER_PATH) . '/' . $this->src;
        }
    }
}
