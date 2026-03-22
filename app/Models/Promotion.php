<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promotion extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'discount_value' => 'float',
        'min_bill_value' => 'float',
        'max_discount_amount' => 'float',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'promotion_product');
    }
}
