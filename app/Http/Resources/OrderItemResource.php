<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_variant_id' => $this->product_variant_id,
            'variant' => new VariantResource($this->whenLoaded('variant')),
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount_amount' => $this->discount_amount,
            'total_price' => $this->total_price,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'warehouse_id' => $this->warehouse_id,
            'notes' => $this->notes,
        ];
    }
}
