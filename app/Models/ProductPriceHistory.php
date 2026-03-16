<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductPriceHistory extends BaseModel
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'import_note_id',
        'import_price',
        'profit_margin',
        'selling_price',
        'created_at'
    ];

    /**
     * Lịch sử giá này thuộc về Sản phẩm nào
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Lịch sử giá này do Phiếu nhập nào thay đổi (có thể null nếu Admin tự đổi giá)
     */
    public function importNote()
    {
        return $this->belongsTo(ImportNote::class);
    }
}
