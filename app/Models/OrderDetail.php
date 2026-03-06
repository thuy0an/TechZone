<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderDetail extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount_applied'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
