<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[ScopedBy([BranchScope::class])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = ['latest_picture'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function salesAgents()
    {
        return $this->belongsToMany(SalesAgent::class, 'sales_sales_agents', 'sales_id', 'sales_agent_id');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'user_task', 'user_id', 'task_id');
    }

    public function branch()
    {
        return $this->morphOne(Branch::class, 'object');
    }

    public function pictures()
    {
        return $this->morphMany(Attachment::class, 'object')->orderBy('id', 'desc');
    }

    public function getLatestPictureAttribute()
    {
        return $this->pictures()->first();
    }

    public function isDeleted(): bool
    {
        return $this->deleted_at != null;
    }

    public function generateSku(): string
    {
        $sku = null;

        while (true) {
            $sku = 'U' . now()->format('ym') . generateRandomAlphabet();

            $exists = self::where(DB::raw('BINARY `sku`'), $sku)->exists();

            if (! $exists) {
                break;
            }
        }

        return $sku;
    }
}
