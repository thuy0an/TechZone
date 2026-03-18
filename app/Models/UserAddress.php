<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAddress extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'receiver_name',
        'receiver_phone',
        'address',
        'is_default',
        'province_id',
        'district_id',
        'ward_code',
        'province_name',
        'district_name',
        'ward_name',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
