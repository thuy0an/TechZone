<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    public function getBrands()
    {
        $brands = Brand::select('id', 'name')->get();
        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }

    public function getCategories()
    {
        $categories = Category::select('id', 'name')->get();
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}