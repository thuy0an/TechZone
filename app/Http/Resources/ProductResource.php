<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $stockStatus = $this->stock_quantity > 0 ? 'In Stock' : 'Out of Stock';

        $specifications = array_filter([
            'Mã sản phẩm' => $this->code,
            'Đơn vị' => $this->unit,
            'Danh mục' => $this->whenLoaded('category', fn() => $this->category->name),
            'Thương hiệu' => $this->whenLoaded('brand', fn() => $this->brand->name),
        ], fn($value) => !is_null($value) && $value !== '');

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'code' => $this->code,
            'name' => $this->name,
            'image' => $this->image ? url($this->image) : null,
            'description' => $this->description,
            'specifications' => $specifications,
            'price' => (float) $this->selling_price,
            'stock_quantity' => $this->stock_quantity,
            'stock_status' => $stockStatus,
            'status' => $this->stock_quantity > 0 ? 'Còn hàng' : 'Hết hàng',
            'category_name' => $this->whenLoaded('category', fn() => $this->category->name),
            'brand_name' => $this->whenLoaded('brand', fn() => $this->brand->name),
        ];
    }
}
