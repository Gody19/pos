@extends('layouts.app')

@section('title', 'Receipt '.$sale->invoice_number)

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-6 flex flex-col items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-center sm:flex-row sm:text-left">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <div>
                    <p class="text-base font-bold text-emerald-800">Sale completed successfully!</p>
                    <p class="text-sm text-emerald-700">Invoice {{ $sale->invoice_number }}</p>
                </div>
            </div>
            <div class="flex gap-2 no-print">
                <button type="button" onclick="window.print()" class="btn-primary">Print</button>
                <a href="{{ route('sales.receipt.download', $sale) }}" class="btn-secondary">PDF</a>
            </div>
        </div>

        <div class="no-print mb-4 flex gap-2">
            <x-button href="{{ route('pos.index') }}">Next Customer</x-button>
            <x-button variant="secondary" href="{{ route('sales.show', $sale) }}">Sale Details</x-button>
        </div>

        {{-- Printable receipt --}}
        <div class="card overflow-hidden">
            <div class="receipt-print mx-auto w-[300px] bg-white p-4">
                <div class="text-center">
                    <p class="text-base font-bold">{{ config('pos.store.name', 'POS Store') }}</p>
                    <p>{{ config('pos.store.address', '') }}</p>
                    <p>{{ config('pos.store.phone', '') }} {{ config('pos.store.tin', '') }}</p>
                </div>

                <div class="my-3 border-t border-dashed border-gray-400"></div>

                <div class="space-y-1">
                    <div class="r-row"><span>Invoice:</span><span class="font-bold">{{ $sale->invoice_number }}</span></div>
                    <div class="r-row"><span>Receipt:</span><span>{{ $receipt->receipt_number }}</span></div>
                    <div class="r-row"><span>Cashier:</span><span>{{ $sale->user?->name }}</span></div>
                    <div class="r-row"><span>Customer:</span><span>{{ $sale->customer?->full_name ?? 'Walk-in' }}</span></div>
                    <div class="r-row"><span>Date:</span><span>{{ $sale->sold_at?->format('d M Y H:i') }}</span></div>
                </div>

                <div class="my-3 border-t border-dashed border-gray-400"></div>

                <div class="space-y-2">
                    @foreach ($sale->items as $item)
                        <div>
                            <p>{{ $item->product?->name ?? 'Item' }}
                                @if ((float) $item->discount > 0)
                                    <span class="text-gray-500">(-{{ number_format($item->discount, 2) }})</span>
                                @endif
                            </p>
                            <div class="r-row text-gray-600">
                                <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 2) }}</span>
                                <span class="font-semibold text-gray-900">{{ number_format($item->total, 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="my-3 border-t border-dashed border-gray-400"></div>

                <div class="space-y-1">
                    <div class="r-row"><span>Subtotal</span><span>{{ number_format($sale->subtotal, 2) }}</span></div>
                    @if ((float) $sale->discount > 0)
                        <div class="r-row"><span>Discount</span><span>-{{ number_format($sale->discount, 2) }}</span></div>
                    @endif
                    @if ((float) $sale->tax > 0)
                        <div class="r-row"><span>Tax</span><span>{{ number_format($sale->tax, 2) }}</span></div>
                    @endif
                    <div class="r-row mt-1 border-t border-dashed border-gray-400 pt-1 text-sm font-bold">
                        <span>TOTAL</span><span>{{ number_format($sale->total, 2) }}</span>
                    </div>
                    <div class="r-row"><span>Paid</span><span>{{ number_format($sale->amount_paid, 2) }}</span></div>
                    <div class="r-row"><span>Change</span><span>{{ number_format($sale->change_due, 2) }}</span></div>
                </div>

                <div class="my-3 border-t border-dashed border-gray-400"></div>

                <div class="space-y-1">
                    @foreach ($sale->payments as $payment)
                        <div class="r-row">
                            <span class="capitalize">{{ $payment->method }} {{ $payment->reference }}</span>
                            <span>{{ number_format($payment->amount, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                @if ($sale->customer)
                    <p class="mt-2">Loyalty points: {{ $sale->customer->loyalty_points }}</p>
                @endif

                <div class="my-3 border-t border-dashed border-gray-400"></div>

                <div class="text-center">
                    <img src="{{ $qrDataUri }}" alt="QR" class="mx-auto h-20 w-20">
                </div>

                <div class="mt-3 text-center text-gray-600">
                    {{ config('pos.store.footer', 'Thank you for shopping with us!') }}<br>
                    Powered by POS System
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
<style>
    @media print {
        body {
            background: white !important;
        }
        main {
            padding: 0 !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endsection