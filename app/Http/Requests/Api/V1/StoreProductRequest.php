<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // We'll add policy-based authorization later
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'type' => 'required|in:simple,configurable',
            'description' => 'nullable|string',
            'sku_prefix' => 'nullable|string|max:50',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }
}
