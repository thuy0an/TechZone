<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ImportNoteDetail;
use App\Services\CloudinaryService;

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

    public function getImageAttribute($value)
    {
        return app(CloudinaryService::class)->buildUrl($value);
    }

    public function setImageAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['image'] = null;
            return;
        }

        $storedValue = (string) $value;

        if (str_starts_with($storedValue, 'http://') || str_starts_with($storedValue, 'https://')) {
            $baseUrl = rtrim((string) config('services.cloudinary.base_url'), '/') . '/';

            if (str_starts_with($storedValue, $baseUrl)) {
                $storedValue = substr($storedValue, strlen($baseUrl));
            }
        }

        $this->attributes['image'] = ltrim($storedValue, '/');
    }

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

    public function importNoteDetails()
    {
        return $this->hasMany(ImportNoteDetail::class, 'product_id');
    }

    public function priceHistories()
    {
        return $this->hasMany(ProductPriceHistory::class)->orderBy('created_at', 'desc');
    }
}
