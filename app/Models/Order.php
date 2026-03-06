<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'promotion_id',
        'order_date',
        'status',
        'shipping_address',
        'receiver_name',
        'receiver_phone',
        'payment_method',
        'total_amount'
    ];

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
