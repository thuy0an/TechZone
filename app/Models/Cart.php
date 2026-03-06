<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends BaseModel
{
    protected $fillable = ['user_id'];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}
