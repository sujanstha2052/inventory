<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreInvoiceRequest;
use App\Http\Requests\Api\V1\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['order.customer']);

        // Filter by status
        if ($request->has('filter.status')) {
            $query->where('status', $request->input('filter.status'));
        }
        // Filter by customer via order
        if ($request->has('filter.customer_id')) {
            $query->whereHas('order', fn ($q) => $q->where('customer_id', $request->input('filter.customer_id')));
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
        $allowedSorts = ['invoice_number', 'grand_total', 'due_date', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $invoices = $query->paginate($request->input('per_page', 15));

        return InvoiceResource::collection($invoices);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('order.customer', 'order.items.variant');

        return new InvoiceResource($invoice);
    }

    public function store(StoreInvoiceRequest $request)
    {
        $data = $request->validated();
        $order = Order::findOrFail($data['order_id']);

        // Auto-generate invoice number
        $latestInvoiceId = Invoice::max('id') ?? 0;
        $data['invoice_number'] = 'INV-'.now()->format('Y').'-'.str_pad($latestInvoiceId + 1, 5, '0', STR_PAD_LEFT);
        // Copy financials from order
        $data['subtotal'] = $order->subtotal;
        $data['tax_amount'] = $order->tax_amount;
        $data['discount_amount'] = $order->discount_amount;
        $data['grand_total'] = $order->grand_total;
        $data['issued_at'] = $data['issued_at'] ?? now();
        $data['created_by'] = auth()->id();
        // status defaults to unpaid if not provided

        $invoice = Invoice::create($data);

        return new InvoiceResource($invoice->load('order.customer'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();
        $invoice->update($data);

        return new InvoiceResource($invoice->fresh('order.customer'));
    }

    public function destroy(Invoice $invoice)
    {
        // Soft delete
        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully']);
    }

    /**
     * Generate or regenerate PDF for an invoice.
     */
    public function generatePdf(Invoice $invoice)
    {
        $invoice->load('order.customer', 'order.items.variant');

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'))
            ->setPaper('a4', 'portrait');

        $path = 'invoices/'.$invoice->invoice_number.'.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        $invoice->update([
            'file_path' => $path,
            'issued_at' => $invoice->issued_at ?? now(),
        ]);

        return response()->json([
            'message' => 'PDF generated successfully',
            'file_url' => asset('storage/'.$path),
        ]);
    }
}
