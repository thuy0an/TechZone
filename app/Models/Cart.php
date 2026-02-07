<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'status',
        'total_price'
    ];

    // Quan hệ với CartItem
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    // Quan hệ với User (nếu đã đăng nhập)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}