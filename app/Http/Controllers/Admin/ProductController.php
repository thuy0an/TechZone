<?php

namespace App\Http\Controllers\Admin;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Http\Controllers\Api\BaseApiController; 
use App\Services\Interfaces\ProductServiceInterface;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Response; 

class ProductController extends BaseApiController
{
    protected $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Trang danh sách (Trả về View cùng dữ liệu Models)
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(10);
        $categories = Category::where('status', 1)->get();
        $brands = Brand::where('status', 1)->get();

        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * API Thêm mới 
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();

            if ($request->hasFile('image')) {
            // Upload lên Cloudinary và lấy link HTTPS an toàn
            $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();
            
            // Lưu link full (https://res.cloudinary.com/...) vào DB
            $data['image'] = $uploadedFileUrl;
        }

            $product = $this->productService->create($data);

            return $this->successResponse($product, 'Thêm sản phẩm thành công', Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * API Cập nhật
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();

            if ($request->hasFile('image')) {
            // (Tùy chọn) xóa ảnh cũ trên Cloudinary tại đây nếu muốn tiết kiệm dung lượng
            
            // Upload ảnh mới
            $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();
            $data['image'] = $uploadedFileUrl;
        }

            $product = $this->productService->update($id, $data);

            return $this->successResponse($product, 'Cập nhật thành công');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * API Xóa
     */
    public function destroy($id)
    {
        try {
            $this->productService->delete($id);
            return $this->successResponse(null, 'Đã xóa sản phẩm thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Không thể xóa sản phẩm này', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}