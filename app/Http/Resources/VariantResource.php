<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'name' => $this->name,
            'variant_attributes' => $this->variant_attributes,
            'purchase_price' => $this->purchase_price,
            'selling_price' => $this->selling_price,
            'barcode' => $this->barcode,
            'is_default' => $this->is_default,
            'weight' => $this->weight,
            'width' => $this->width,
            'height' => $this->height,
            'depth' => $this->depth,
            'low_stock_threshold' => $this->low_stock_threshold,
            'is_active' => $this->is_active,
            'product' => new ProductResource($this->whenLoaded('product')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
