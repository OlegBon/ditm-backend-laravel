<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'delivery_method',
        'np_city',
        'np_city_ref',
        'np_branch',
        'np_branch_ref',
        'total',
        'status',
    ];

    // Зв'язок "один до багатьох" з OrderDetails
    public function items()
    {
        return $this->hasMany(OrdersDetails::class, 'order_id', 'id');
    }
}
