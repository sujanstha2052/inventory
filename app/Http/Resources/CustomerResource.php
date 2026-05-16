<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
            'tax_number' => $this->tax_number,
            'is_active' => $this->is_active,
            'outstanding_balance' => $this->outstanding_balance,
            'total_sales' => $this->total_sales,
            'group' => new CustomerGroupResource($this->whenLoaded('group')),
        ];
    }
}
