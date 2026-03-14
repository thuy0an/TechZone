<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportNote extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'admin_id',       // Người tạo phiếu nhập
        'supplier_id',    // Nhà cung cấp 
        'import_date',    // Ngày nhập
        'status',         // Trạng thái: pending, completed
        'total_cost'      // Tổng tiền nhập
    ];

    // Ép kiểu cho ngày tháng để dễ format
    protected $casts = [
        'import_date' => 'datetime',
    ];

    /**
     * Phiếu nhập này do Admin/Nhân viên nào tạo 
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Phiếu nhập này thuộc về Nhà cung cấp nào
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Một phiếu nhập có nhiều chi tiết phiếu nhập (Import Note Details)
     */
    public function details()
    {
        return $this->hasMany(ImportNoteDetail::class, 'import_note_id');
    }
}