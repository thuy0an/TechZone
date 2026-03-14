<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportNoteDetail extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'import_note_id',
        'product_id',
        'quantity',
        'import_price'
    ];

    /**
     * Chi tiết này thuộc về Phiếu nhập nào
     */
    public function importNote()
    {
        return $this->belongsTo(ImportNote::class, 'import_note_id');
    }

    /**
     * Chi tiết này đang nhập cho Sản phẩm nào
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
