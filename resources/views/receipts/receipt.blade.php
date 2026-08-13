<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $sale->invoice_number }}</title>
    <style>
        * { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        body { width: 100%; font-size: 10px; color: #111; padding: 8px; }
        .center { text-align: center; }
        .store-name { font-size: 15px; font-weight: bold; margin: 2px 0; }
        .muted { color: #555; font-size: 9px; }
        .divider { border-top: 1px dashed #333; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; width: 100%; }
        .row span { display: inline-block; }
        .items { width: 100%; }
        .items .line { margin: 2px 0; }
        .totals { margin-top: 4px; }
        .totals .grand { font-size: 12px; font-weight: bold; }
        .payments { margin-top: 4px; }
        .qr { text-align: center; margin: 6px 0; }
        .qr img { width: 80px; height: 80px; }
        .footer { margin-top: 6px; }
        .b { font-weight: bold; }
        .mt { margin-top: 4px; }
    </style>
</head>
<body>
    <div class="center">
        <div class="store-name">{{ config('pos.store.name', 'POS Store') }}</div>
        <div class="muted">{{ config('pos.store.address', '') }}</div>
        <div class="muted">{{ config('pos.store.phone', '') }} {{ config('pos.store.tin', '') }}</div>
    </div>

    <div class="divider"></div>

    <div class="row"><span>Invoice:</span><span class="b">{{ $sale->invoice_number }}</span></div>
    <div class="row"><span>Receipt:</span><span>{{ $receipt->receipt_number ?? '' }}</span></div>
    <div class="row"><span>Cashier:</span><span>{{ $sale->user->name ?? '' }}</span></div>
    <div class="row"><span>Customer:</span><span>{{ $sale->customer->full_name ?? 'Walk-in' }}</span></div>
    <div class="row"><span>Date:</span><span>{{ $sale->sold_at?->format('d M Y H:i') }}</span></div>

    <div class="divider"></div>

    <div class="items">
        @foreach ($sale->items as $item)
            <div class="line">
                <div>{{ $item->product->name ?? 'Item' }}
                    @if ((float) $item->discount > 0)
                        <span class="muted"> (-{{ number_format($item->discount, 2) }})</span>
                    @endif
                </div>
                <div class="row muted">
                    <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 2) }}</span>
                    <span class="b">{{ number_format($item->total, 2) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="divider"></div>

    <div class="totals">
        <div class="row"><span>Subtotal</span><span>{{ number_format($sale->subtotal, 2) }}</span></div>
        @if ((float) $sale->discount > 0)
            <div class="row"><span>Discount</span><span>-{{ number_format($sale->discount, 2) }}</span></div>
        @endif
        @if ((float) $sale->tax > 0)
            <div class="row"><span>Tax</span><span>{{ number_format($sale->tax, 2) }}</span></div>
        @endif
        <div class="row grand mt"><span>TOTAL</span><span>{{ number_format($sale->total, 2) }}</span></div>
        <div class="row mt"><span>Paid</span><span>{{ number_format($sale->amount_paid, 2) }}</span></div>
        <div class="row"><span>Change</span><span>{{ number_format($sale->change_due, 2) }}</span></div>
    </div>

    <div class="divider"></div>

    <div class="payments">
        @foreach ($sale->payments as $payment)
            <div class="row">
                <span>{{ ucfirst($payment->method) }} {{ $payment->reference ?? '' }}</span>
                <span>{{ number_format($payment->amount, 2) }}</span>
            </div>
        @endforeach
    </div>

    @if ($sale->customer)
        <div class="mt">Loyalty points: {{ $sale->customer->loyalty_points }}</div>
    @endif

    <div class="divider"></div>

    <div class="qr">
        <img src="{{ $qrDataUri }}" alt="QR">
    </div>

    <div class="center muted footer">
        {{ config('pos.store.footer', 'Thank you for shopping with us!') }}<br>
        Powered by POS System
    </div>
</body>
</html>