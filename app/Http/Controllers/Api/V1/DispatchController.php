<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDispatchRequest;
use App\Http\Requests\Api\V1\UpdateDispatchRequest;
use App\Http\Resources\DispatchResource;
use App\Models\Dispatch;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispatchController extends Controller
{
    public function index(Request $request)
    {
        $query = Dispatch::with(['order.customer', 'warehouse']);

        if ($request->has('filter.order_id')) {
            $query->where('order_id', $request->input('filter.order_id'));
        }
        if ($request->has('filter.status')) {
            $query->where('status', $request->input('filter.status'));
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts = ['dispatch_number', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $dispatches = $query->paginate($request->input('per_page', 15));

        return DispatchResource::collection($dispatches);
    }

    public function show(Dispatch $dispatch)
    {
        $dispatch->load(['order.customer', 'warehouse', 'items.orderItem.variant', 'items.stock']);

        return new DispatchResource($dispatch);
    }

    public function store(StoreDispatchRequest $request)
    {
        $data = $request->validated();
        $itemsData = $data['items'];
        unset($data['items']);

        $dispatch = DB::transaction(function () use ($data, $itemsData) {
            $data['dispatch_number'] = 'DISP-'.now()->format('Y').'-'.str_pad(Dispatch::max('id') + 1, 5, '0', STR_PAD_LEFT);
            $data['status'] = 'pending';
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $dispatch = Dispatch::create($data);

            foreach ($itemsData as $item) {
                $dispatch->items()->create($item);
            }

            return $dispatch;
        });

        return new DispatchResource($dispatch->load('order.customer', 'warehouse', 'items.orderItem.variant', 'items.stock'));
    }

    public function update(UpdateDispatchRequest $request, Dispatch $dispatch)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();
        $dispatch->update($data);

        return new DispatchResource($dispatch->fresh());
    }

    public function destroy(Dispatch $dispatch)
    {
        $dispatch->delete();

        return response()->json(['message' => 'Dispatch deleted successfully']);
    }

    /**
     * Confirm dispatch: deduct stock and create stock movements.
     */
    public function confirm(Dispatch $dispatch)
    {
        if ($dispatch->status !== 'pending') {
            return response()->json(['message' => 'Dispatch is already confirmed or cancelled'], 422);
        }

        DB::transaction(function () use ($dispatch) {
            foreach ($dispatch->items as $item) {
                $stockBatch = Stock::findOrFail($item->stock_id);
                if ($stockBatch->quantity < $item->quantity) {
                    throw new \Exception("Insufficient stock in batch {$stockBatch->batch_number}");
                }

                $stockBatch->decrement('quantity', $item->quantity);

                StockMovement::create([
                    'stock_id' => $item->stock_id,
                    'product_variant_id' => $stockBatch->product_variant_id,
                    'warehouse_id' => $stockBatch->warehouse_id,
                    'type' => 'out',
                    'quantity' => $item->quantity,
                    'unit_cost' => $stockBatch->unit_cost,
                    'reference_type' => get_class($dispatch),
                    'reference_id' => $dispatch->id,
                    'notes' => "Dispatch {$dispatch->dispatch_number}",
                    'created_by' => auth()->id(),
                ]);
            }

            $dispatch->update([
                'status' => 'in_transit',
                'dispatched_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Dispatch confirmed, stock deducted']);
    }
}
