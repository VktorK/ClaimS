<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'short_title' => $this->short_title,
            'address' => $this->address,
            'ogrn' => $this->ogrn,
            'has_ogrn' => $this->hasOgrn(),
            'products_count' => $this->products_count,
            'total_value' => $this->total_value,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
            
            // Связанные данные
            'products' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'title' => $product->title,
                        'price' => $product->price,
                        'formatted_price' => $product->formatted_price,
                        'created_at' => $product->created_at ? $product->created_at->toISOString() : null,
                    ];
                });
            }),
        ];
    }
}
