<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'price',
        'thumbnail',
        'warrantyInformation'
    ];


    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'product_id', 'id');
    }
}
