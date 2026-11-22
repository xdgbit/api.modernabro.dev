<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'name'                => $this->name,
            'sku'                 => $this->sku,
            'cover_image'         => $this->cover_image,
            'description'         => $this->description,
            'display_rating'      => $this->display_rating,

            // Pricing
            'price'               => $this->price,
            'final_price'         => $this->final_price,
            'discount_percent'    => $this->discount_percent,

            // Inventory
            'stock'               => $this->stock,
            'low_stock_threshold' => $this->low_stock_threshold,
            'stock_status'        => $this->stockStatus(),

            // Status
            'is_active'           => $this->status === 'active',

            // Relations
            'brand_title'         => $this->whenLoaded('brand', fn() => $this->brand?->title),
            'category_title'      => $this->whenLoaded('category', fn() => $this->category?->title),
        ];
    }

    private function stockStatus(): string
    {
        if ($this->stock <= 0)
        {
            return 'out_of_stock';
        }

        if ($this->stock <= $this->low_stock_threshold)
        {
            return 'low_stock';
        }

        return 'in_stock';
    }
}