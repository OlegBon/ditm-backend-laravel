<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecentlyViewedProducts extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'product_id', 'viewed_at'];
    protected $table = 'recently_viewed_products'; // Вказуємо назву таблиці, тому що RecentlyViewedProducts не відповідає стандартному формату

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
