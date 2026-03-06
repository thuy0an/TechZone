<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends BaseModel
{
    use HasFactory;

    protected $fillable = ['name', 'logo'];

    public function products(){
        return $this->hasMany(Product::class);
    }
}