<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'brand_id', 'code', 'name', 'image', 
        'description', 'specifications', 'is_hidden', 'has_serial',
        'stock_quantity', 'current_import_price', 'specific_profit_margin'
    ];

    protected $casts = [
        'specifications' => 'array', 
        'is_hidden' => 'boolean',
        'has_serial' => 'boolean',
        'current_import_price' => 'decimal:2',
        'specific_profit_margin' => 'double',
    ];

    protected function getSearchableFields(): array
    {
        return ['name', 'code']; 
    }

    // --- RELATIONSHIPS ---

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}
