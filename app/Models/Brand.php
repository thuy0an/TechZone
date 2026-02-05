<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends BaseModel
{
    protected $fillable = [
        'name', 
        'logo',
        'status'
    ];

    protected function getSearchableFields(): array {
        return ['name'];
    }
}
