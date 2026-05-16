<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreProductRequest;
use App\Http\Requests\Api\V1\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'category', 'unit']);

        // Filtering
        if ($request->has('filter.brand_id')) {
            $query->where('brand_id', $request->input('filter.brand_id'));
        }
        if ($request->has('filter.category_id')) {
            $query->where('category_id', $request->input('filter.category_id'));
        }
        if ($request->has('filter.type')) {
            $query->where('type', $request->input('filter.type'));
        }
        if ($request->has('filter.is_active')) {
            $query->where('is_active', $request->boolean('filter.is_active'));
        }

        // Sorting
        $sortField = $request->input('sort', 'name');
        $sortDirection = $request->input('direction', 'asc');
        $allowedSorts = ['name', 'created_at', 'updated_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $products = $query->paginate($request->input('per_page', 15));

        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        $product->load(['brand', 'category', 'unit', 'variants']); // load variants for detail view

        return new ProductResource($product);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $product = Product::create($data);

        return new ProductResource($product->load(['brand', 'category', 'unit']));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $product->update($data);

        return new ProductResource($product->fresh(['brand', 'category', 'unit']));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
