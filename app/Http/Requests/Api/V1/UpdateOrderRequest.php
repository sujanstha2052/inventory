<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'sometimes|exists:customers,id',
            'status' => 'sometimes|in:draft,confirmed,processing,dispatched,delivered,cancelled,returned',
            'notes' => 'nullable|string',
            'items' => 'sometimes|array|min:1',
            'items.*.id' => 'nullable|exists:order_items,id', // if updating existing item
            'items.*.product_variant_id' => 'required_with:items|exists:product_variants,id',
            'items.*.quantity' => 'required_with:items|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.warehouse_id' => 'nullable|exists:warehouses,id',
            'items.*.notes' => 'nullable|string',
        ];
    }
}
