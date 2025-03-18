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
        'total',
    ];

    // Зв'язок "один до багатьох" з OrderDetails
    public function items()
    {
        return $this->hasMany(OrdersDetails::class);
    }
}
