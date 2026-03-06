<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends BaseModel
{
    protected $fillable = ['cart_id', 'product_id', 'quantity', 'price_at_addition'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
