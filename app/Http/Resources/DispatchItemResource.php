<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DispatchItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_item_id' => $this->order_item_id,
            'stock_id' => $this->stock_id,
            'quantity' => $this->quantity,
        ];
    }
}
