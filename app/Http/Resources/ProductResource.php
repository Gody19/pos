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
            'category' => new CategoryResource($this->whenLoaded('category')),
            'category_id' => $this->category_id,
            'barcode' => $this->barcode,
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description,
            'cost_price' => (float) $this->cost_price,
            'selling_price' => (float) $this->selling_price,
            'tax_rate' => (float) $this->tax_rate,
            'image' => $this->image ? asset('storage/'.$this->image) : null,
            'status' => $this->status,
            'inventory' => new InventoryResource($this->whenLoaded('inventory')),
            'stock' => $this->stockQuantity(),
            'is_out_of_stock' => $this->isOutOfStock(),
            'is_low_stock' => $this->isLowStock(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
