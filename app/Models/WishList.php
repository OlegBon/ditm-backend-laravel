<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishList extends Model
{
    protected $fillable = ['user_id', 'product_id'];
    protected $table = 'wishlists'; // Вказуємо назву таблиці, тому що WishList не відповідає стандартному формату

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
