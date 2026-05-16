<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreOrderRequest;
use App\Http\Requests\Api\V1\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'items.variant']);

        // Filter by customer
        if ($request->has('filter.customer_id')) {
            $query->where('customer_id', $request->input('filter.customer_id'));
        }
        // Filter by status
        if ($request->has('filter.status')) {
            $query->where('status', $request->input('filter.status'));
        }
        // Date range
        if ($request->has('filter.from_date')) {
            $query->whereDate('created_at', '>=', $request->input('filter.from_date'));
        }
        if ($request->has('filter.to_date')) {
            $query->whereDate('created_at', '<=', $request->input('filter.to_date'));
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts = ['order_number', 'grand_total', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $orders = $query->paginate($request->input('per_page', 15));

        return OrderResource::collection($orders);
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'items.variant', 'items.warehouse']);

        return new OrderResource($order);
    }

    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();
        $itemsData = $data['items'];
        unset($data['items']);

        $order = DB::transaction(function () use ($data, $itemsData) {
            // Auto-generate order number
            $latestOrderId = Order::max('id') ?? 0;
            $data['order_number'] = 'ORD-'.now()->format('Y').'-'.str_pad($latestOrderId + 1, 5, '0', STR_PAD_LEFT);
            $data['status'] = $data['status'] ?? 'draft';
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            // Initialize totals
            $subtotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;

            $order = Order::create($data);

            foreach ($itemsData as $item) {
                // Set default unit_price from variant if not provided
                if (! isset($item['unit_price']) || $item['unit_price'] === null) {
                    $variant = ProductVariant::findOrFail($item['product_variant_id']);
                    $item['unit_price'] = $variant->selling_price;
                }
                $item['tax_rate'] = $item['tax_rate'] ?? 15;
                $item['discount_amount'] = $item['discount_amount'] ?? 0;

                $lineTotal = ($item['unit_price'] * $item['quantity']) - $item['discount_amount'];
                $taxAmount = $lineTotal * ($item['tax_rate'] / 100);

                $item['total_price'] = $lineTotal;
                $item['tax_amount'] = $taxAmount;

                $subtotal += $item['unit_price'] * $item['quantity'];
                $totalDiscount += $item['discount_amount'];
                $totalTax += $taxAmount;

                $order->items()->create($item);
            }

            // Update order totals
            $order->update([
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'grand_total' => $subtotal - $totalDiscount + $totalTax,
            ]);

            return $order;
        });

        return new OrderResource($order->load('customer', 'items.variant'));
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $data = $request->validated();
        $itemsData = $data['items'] ?? null;
        unset($data['items']);

        DB::transaction(function () use ($order, $data, $itemsData) {
            $data['updated_by'] = auth()->id();
            $order->update($data);

            // If items are provided, replace all items
            if ($itemsData !== null) {
                $order->items()->delete();

                $subtotal = 0;
                $totalDiscount = 0;
                $totalTax = 0;

                foreach ($itemsData as $item) {
                    if (! isset($item['unit_price']) || $item['unit_price'] === null) {
                        $variant = ProductVariant::findOrFail($item['product_variant_id']);
                        $item['unit_price'] = $variant->selling_price;
                    }
                    $item['tax_rate'] = $item['tax_rate'] ?? 15;
                    $item['discount_amount'] = $item['discount_amount'] ?? 0;

                    $lineTotal = ($item['unit_price'] * $item['quantity']) - $item['discount_amount'];
                    $taxAmount = $lineTotal * ($item['tax_rate'] / 100);

                    $item['total_price'] = $lineTotal;
                    $item['tax_amount'] = $taxAmount;

                    $subtotal += $item['unit_price'] * $item['quantity'];
                    $totalDiscount += $item['discount_amount'];
                    $totalTax += $taxAmount;

                    $order->items()->create($item);
                }

                $order->update([
                    'subtotal' => $subtotal,
                    'discount_amount' => $totalDiscount,
                    'tax_amount' => $totalTax,
                    'grand_total' => $subtotal - $totalDiscount + $totalTax,
                ]);
            }
        });

        return new OrderResource($order->fresh(['customer', 'items.variant']));
    }

    public function destroy(Order $order)
    {
        // Only allow deletion for draft orders? For now, allow any.
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
}
