<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel
{
    use HasFactory;

    protected $fillable = ['name'];

    public function products(){
        return $this->hasMany(Product::class);
    }
}


