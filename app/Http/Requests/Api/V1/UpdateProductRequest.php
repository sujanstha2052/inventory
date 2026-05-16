<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:products,slug,'.$productId,
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'sometimes|exists:units,id',
            'type' => 'sometimes|in:simple,configurable',
            'description' => 'nullable|string',
            'sku_prefix' => 'nullable|string|max:50',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }
}
