<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'title',
        'price',
        'count',
    ];

    // Зв'язок "багато до одного" з Orders
    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'id');
    }
}
