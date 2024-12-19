<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'classification_code_product');
    }

}
