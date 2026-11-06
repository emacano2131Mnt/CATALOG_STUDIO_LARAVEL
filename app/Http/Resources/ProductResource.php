<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $src = $this->image_src;

        return [
            'id' => $this->id,
            'catalog_id' => $this->catalog_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'position' => $this->position,
            'image_url' => $src ? (str_starts_with($src, 'http') ? $src : url($src)) : null,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'catalog' => new CatalogResource($this->whenLoaded('catalog')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
