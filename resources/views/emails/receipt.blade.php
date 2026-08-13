<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your Receipt {{ $sale->invoice_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111;">
    <h2>{{ config('pos.store.name', 'POS Store') }}</h2>
    <p>Thank you for your purchase!</p>
    <table style="width: 100%; border-collapse: collapse; margin-top: 12px;">
        <thead>
            <tr style="text-align: left; border-bottom: 1px solid #ddd;">
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Item' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p style="margin-top: 12px;">
        <strong>Invoice:</strong> {{ $sale->invoice_number }}<br>
        <strong>Date:</strong> {{ $sale->sold_at?->format('d M Y H:i') }}<br>
        <strong>Total:</strong> {{ number_format($sale->total, 2) }}
    </p>
    <p>Your receipt PDF is attached.</p>
</body>
</html>