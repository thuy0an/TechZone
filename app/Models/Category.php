<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'default_profit_margin',
        'spec_template'
    ];

    protected $casts = [
        'spec_template' => 'array',
        'default_profit_margin' => 'double'
    ];

    protected function getSearchableFields(): array
    {
        return ['name'];
    }
}
