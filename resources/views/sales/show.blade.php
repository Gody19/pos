@extends('layouts.app')

@section('title', 'Sale '.$sale->invoice_number)

@section('content')
    <x-page-header title="Sale Details" :description="$sale->invoice_number.' · '.$sale->sold_at?->format('d M Y, H:i')"
        :crumbs="['Sales' => route('sales.index'), $sale->invoice_number => null]">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('sales.receipt', $sale) }}">Receipt</x-button>
            <x-button variant="secondary" href="{{ route('sales.receipt.download', $sale) }}">Download PDF</x-button>
            @if ($sale->isCompleted() && auth()->user()->can('cancel sales'))
                <form method="POST" action="{{ route('sales.cancel', $sale) }}" data-confirm="Cancel this sale and restore stock?">
                    @csrf
                    <input type="hidden" name="reason" value="Cancelled from sales page">
                    <button type="submit" class="btn-danger">Cancel Sale</button>
                </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if (! $sale->isCompleted())
        <x-alert type="warning" class="mb-4">This sale has been cancelled. Stock was restored to inventory.</x-alert>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Items">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Tax</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td class="font-semibold text-gray-900">{{ $item->product?->name ?? 'Unknown' }}</td>
                                    <td class="text-right">{{ $item->quantity }}</td>
                                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->tax_amount, 2) }}</td>
                                    <td class="text-right font-semibold">{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-bold">
                                <td colspan="4" class="text-right">Subtotal</td>
                                <td class="text-right">{{ number_format($sale->subtotal, 2) }}</td>
                            </tr>
                            @if ((float) $sale->discount > 0)
                                <tr class="font-semibold">
                                    <td colspan="4" class="text-right text-red-600">Discount</td>
                                    <td class="text-right text-red-600">-{{ number_format($sale->discount, 2) }}</td>
                                </tr>
                            @endif
                            <tr class="font-semibold">
                                <td colspan="4" class="text-right">Tax</td>
                                <td class="text-right">{{ number_format($sale->tax, 2) }}</td>
                            </tr>
                            <tr class="bg-brand-50 text-lg font-bold text-brand-800">
                                <td colspan="4" class="text-right">TOTAL</td>
                                <td class="text-right">{{ number_format($sale->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>

            <x-card title="Payments">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>Reference</th>
                            <th class="text-right">Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->payments as $payment)
                            <tr>
                                <td class="capitalize font-semibold">{{ $payment->method }}</td>
                                <td class="text-xs text-gray-500">{{ $payment->reference ?? '—' }}</td>
                                <td class="text-right font-semibold">{{ number_format($payment->amount, 2) }}</td>
                                <td>
                                    @if ($payment->status === 'completed')
                                        <x-badge color="green">Completed</x-badge>
                                    @else
                                        <x-badge color="red">Refunded</x-badge>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-3 space-y-1 border-t border-gray-100 pt-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Amount paid</span><span class="font-semibold">{{ number_format($sale->amount_paid, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Change returned</span><span class="font-semibold">{{ number_format($sale->change_due, 2) }}</span></div>
                </div>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Order info">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Cashier</dt>
                        <dd class="font-semibold text-gray-900">{{ $sale->user?->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Customer</dt>
                        <dd class="text-gray-900">{{ $sale->customer?->full_name ?? 'Walk-in' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Shift</dt>
                        <dd class="text-gray-900">{{ $sale->shift ? 'Shift #'.$sale->shift_id : '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Payment status</dt>
                        <dd>
                            @if ($sale->isCompleted())
                                <x-badge color="green">Completed</x-badge>
                            @else
                                <x-badge color="red">Cancelled</x-badge>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Receipt no.</dt>
                        <dd class="font-mono text-xs text-gray-900">{{ $sale->receipt?->receipt_number ?? '—' }}</dd>
                    </div>
                </dl>
            </x-card>

            @if ($sale->loyaltyTransactions->isNotEmpty())
                <x-card title="Loyalty">
                    <div class="space-y-2 text-sm">
                        @foreach ($sale->loyaltyTransactions as $txn)
                            <div class="flex justify-between">
                                <span class="capitalize text-gray-600">{{ $txn->type }}</span>
                                <span class="font-semibold {{ $txn->type === 'earned' ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $txn->type === 'earned' ? '+' : '-' }}{{ $txn->points }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif

            @if ($sale->notes)
                <x-card title="Notes">
                    <p class="text-sm text-gray-600">{{ $sale->notes }}</p>
                </x-card>
            @endif
        </div>
    </div>
@endsection