<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller; // Client chỉ cần Controller thường
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Trang chi tiết sản phẩm
     */
    public function show($id)
    {
        $product = Product::with(['category', 'brand'])
            ->where('is_hidden', 0) 
            ->findOrFail($id);

        // Sản phẩm liên quan
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $id)
            ->where('is_hidden', 0)
            ->take(4)
            ->get();

        return view('client.products.detail', compact('product', 'relatedProducts'));
    }
    
    // Sau này có thể thêm hàm search(), filter()... dành riêng cho khách hàng ở đây
}