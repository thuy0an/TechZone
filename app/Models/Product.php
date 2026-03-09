<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'code',
        'name',
        'image',
        'description',
        'unit',
        'initial_quantity',
        'stock_quantity',
        'import_price',
        'profit_margin',
        'selling_price',
        'status',
        'low_stock_threshold',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'product_id');
    }
}
