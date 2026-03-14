<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address'
    ];

    /**
     * Một nhà cung cấp có nhiều phiếu nhập kho
     */
    public function importNotes()
    {
        return $this->hasMany(ImportNote::class);
    }
}
