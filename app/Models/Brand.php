<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends BaseModel
{
    protected $fillable = [
        'name', 
        'logo'
    ];

    protected function getSearchableFields(): array {
        return ['name'];
    }
}
