<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .invoice-info { width: 100%; margin-bottom: 20px; }
        .invoice-info td { padding: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .total { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <h2>{{ $invoice->invoice_number }}</h2>
    </div>

    <table class="invoice-info">
        <tr>
            <td><strong>Customer:</strong> {{ $invoice->order->customer->name }}</td>
            <td><strong>Date:</strong> {{ $invoice->issued_at?->format('Y-m-d') ?? now()->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td><strong>Order:</strong> {{ $invoice->order->order_number }}</td>
            <td><strong>Due Date:</strong> {{ $invoice->due_date?->format('Y-m-d') ?? 'N/A' }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Discount</th>
                <th>Tax</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->order->items as $item)
            <tr>
                <td>{{ $item->variant->sku }}</td>
                <td>{{ $item->variant->name ?? 'N/A' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>${{ number_format($item->unit_price, 2) }}</td>
                <td>${{ number_format($item->discount_amount, 2) }}</td>
                <td>${{ number_format($item->tax_amount, 2) }}</td>
                <td>${{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <p>Subtotal: ${{ number_format($invoice->subtotal, 2) }}</p>
        <p>Discount: ${{ number_format($invoice->discount_amount, 2) }}</p>
        <p>Tax: ${{ number_format($invoice->tax_amount, 2) }}</p>
        <h3>Total: ${{ number_format($invoice->grand_total, 2) }}</h3>
    </div>
</body>
</html>
