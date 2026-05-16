<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DispatchResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'dispatch_number' => $this->dispatch_number,
            'status' => $this->status,
            'order' => new OrderResource($this->whenLoaded('order')),
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'items' => DispatchItemResource::collection($this->whenLoaded('items')),
            'notes' => $this->notes,
            'dispatched_at' => $this->dispatched_at,
            'delivered_at' => $this->delivered_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
