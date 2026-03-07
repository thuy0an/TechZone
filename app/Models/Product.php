<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    use HasFactory, SoftDeletes;

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

    protected $dates = ['deleted_at'];

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
