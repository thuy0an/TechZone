<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'image' => $this->image ? url($this->image) : null,
            'description' => $this->description,
            'price' => (float) $this->selling_price,
            'stock_quantity' => $this->stock_quantity,
            'status' => $this->stock_quantity > 0 ? 'Còn hàng' : 'Hết hàng',
            'category_name' => $this->whenLoaded('category', fn() => $this->category->name),
            'brand_name' => $this->whenLoaded('brand', fn() => $this->brand->name),
        ];
    }
}
