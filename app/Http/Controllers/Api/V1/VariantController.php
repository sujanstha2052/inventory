<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\VariantResource;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductVariant::with(['product']);

        // Filter by product
        if ($request->has('filter.product_id')) {
            $query->where('product_id', $request->input('filter.product_id'));
        }

        // Filter by active
        if ($request->has('filter.is_active')) {
            $query->where('is_active', $request->boolean('filter.is_active'));
        }

        // Sorting
        $sortField = $request->input('sort', 'sku');
        $sortDirection = $request->input('direction', 'asc');
        $allowedSorts = ['sku', 'selling_price', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $variants = $query->paginate($request->input('per_page', 15));

        return VariantResource::collection($variants);
    }

    public function show(ProductVariant $variant)
    {
        $variant->load('product.brand', 'product.category');

        return new VariantResource($variant);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'sku' => 'required|string|unique:product_variants',
            'name' => 'nullable|string|max:255',
            'variant_attributes' => 'nullable|array',
            'purchase_price' => 'nullable|numeric',
            'selling_price' => 'required|numeric',
            'barcode' => 'nullable|string|unique:product_variants',
            'is_default' => 'boolean',
            'weight' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'depth' => 'nullable|numeric',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $variant = ProductVariant::create($data);

        return new VariantResource($variant->load('product'));
    }

    public function update(Request $request, ProductVariant $variant)
    {
        $data = $request->validate([
            'sku' => 'sometimes|string|unique:product_variants,sku,'.$variant->id,
            'name' => 'nullable|string|max:255',
            'variant_attributes' => 'nullable|array',
            'purchase_price' => 'nullable|numeric',
            'selling_price' => 'sometimes|numeric',
            'barcode' => 'nullable|string|unique:product_variants,barcode,'.$variant->id,
            'is_default' => 'boolean',
            'weight' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'depth' => 'nullable|numeric',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['updated_by'] = auth()->id();
        $variant->update($data);

        return new VariantResource($variant->fresh('product'));
    }

    public function destroy(ProductVariant $variant)
    {
        $variant->delete();

        return response()->json(['message' => 'Variant deleted successfully']);
    }
}
