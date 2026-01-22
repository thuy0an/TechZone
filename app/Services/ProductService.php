<?php

namespace App\Services;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\Interfaces\ProductServiceInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
class ProductService extends BaseService implements ProductServiceInterface
{
    public function __construct(ProductRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Override hàm create của BaseService để xử lý Upload Ảnh
     */
    public function create(array $data)
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // Lưu vào thư mục public/products
            $path = $data['image']->store('products', 'public');

            // Lưu đường dẫn vào DB
            $data['image'] = 'storage/' . $path; 
        }

        return $this->repository->create($data);
    }

    /**
     * Override hàm update để xử lý thay đổi ảnh
     */
    public function update($id, array $data)
    {
        $product = $this->repository->findById($id);

        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            // Xóa ảnh cũ 
            if ($product->image && file_exists(public_path($product->image))) {
                
            }

            $path = $data['image']->store('products', 'public');
            $data['image'] = 'storage/' . $path;
        }

        return $this->repository->update($id, $data);
    }
}