<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::where('is_hidden', 0)->latest()->take(8)->get();
        return view('client.home', compact('products'));
    }
}