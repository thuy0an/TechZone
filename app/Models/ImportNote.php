<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportNote extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'supplier_id',
        'import_date',
        'status',
        'completed_at',
        'total_cost',
        'paid_amount'
    ];

    protected $casts = [
        'import_date' => 'datetime',
        'completed_at' => 'datetime',
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
