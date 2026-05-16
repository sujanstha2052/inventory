<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePaymentRequest;
use App\Http\Requests\Api\V1\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['customer', 'allocations.invoice']);

        if ($request->has('filter.customer_id')) {
            $query->where('customer_id', $request->input('filter.customer_id'));
        }
        if ($request->has('filter.payment_method')) {
            $query->where('payment_method', $request->input('filter.payment_method'));
        }
        if ($request->has('filter.from_date')) {
            $query->whereDate('payment_date', '>=', $request->input('filter.from_date'));
        }
        if ($request->has('filter.to_date')) {
            $query->whereDate('payment_date', '<=', $request->input('filter.to_date'));
        }

        $sortField = $request->input('sort', 'payment_date');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts = ['amount', 'payment_date', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $payments = $query->paginate($request->input('per_page', 15));

        return PaymentResource::collection($payments);
    }

    public function show(Payment $payment)
    {
        $payment->load('customer', 'allocations.invoice');

        return new PaymentResource($payment);
    }

    public function store(StorePaymentRequest $request)
    {
        $data = $request->validated();
        $allocationsData = $data['allocations'] ?? [];
        unset($data['allocations']);

        $payment = DB::transaction(function () use ($data, $allocationsData) {
            $data['payment_number'] = 'PAY-'.now()->format('Y').'-'.str_pad(Payment::max('id') + 1, 5, '0', STR_PAD_LEFT);
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $payment = Payment::create($data);

            $totalAllocated = 0;
            foreach ($allocationsData as $alloc) {
                $payment->allocations()->create($alloc);
                $totalAllocated += $alloc['amount'];

                // Update invoice status
                $invoice = Invoice::findOrFail($alloc['invoice_id']);
                $invoiceTotalPaid = $invoice->allocations()->sum('amount');
                if ($invoiceTotalPaid >= $invoice->grand_total) {
                    $invoice->update(['status' => 'paid']);
                } else {
                    $invoice->update(['status' => 'partially_paid']);
                }
            }

            // Update customer outstanding balance
            if ($totalAllocated > 0) {
                $customer = Customer::find($data['customer_id']);
                $customer->decrement('outstanding_balance', $totalAllocated);
            }

            return $payment;
        });

        return new PaymentResource($payment->load('customer', 'allocations.invoice'));
    }

    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        // For simplicity, only update the payment header, not allocations
        $data = $request->validated();
        $data['updated_by'] = auth()->id();
        $payment->update($data);

        return new PaymentResource($payment->fresh('customer', 'allocations.invoice'));
    }

    public function destroy(Payment $payment)
    {
        // Soft delete; we could also rollback allocations, but we'll leave them for audit
        $payment->delete();

        return response()->json(['message' => 'Payment deleted successfully']);
    }
}
