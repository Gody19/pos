@extends('layouts.app')

@section('title', 'Sales')

@section('content')
    <x-page-header title="Sales" description="Browse completed and cancelled transactions.">
        <x-slot:actions>
            @can('create sales')
                <x-button href="{{ route('pos.index') }}">New Sale</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <x-slot:actions>
            <form method="GET" class="flex flex-col gap-2 lg:flex-row lg:items-center">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice number…"
                    class="input sm:w-48" autocomplete="off">
                <select name="payment_status" class="input sm:w-36" onchange="this.form.submit()">
                    <option value="">Any status</option>
                    <option value="completed" @selected(request('payment_status') === 'completed')>Completed</option>
                    <option value="cancelled" @selected(request('payment_status') === 'cancelled')>Cancelled</option>
                </select>
                <select name="payment_method" class="input sm:w-32" onchange="this.form.submit()">
                    <option value="">Any method</option>
                    <option value="cash" @selected(request('payment_method') === 'cash')>Cash</option>
                    <option value="card" @selected(request('payment_method') === 'card')>Card</option>
                    <option value="mobile" @selected(request('payment_method') === 'mobile')>Mobile</option>
                    <option value="qr" @selected(request('payment_method') === 'qr')>QR</option>
                </select>
                <input type="date" name="from" value="{{ request('from') }}" class="input sm:w-40">
                <input type="date" name="to" value="{{ request('to') }}" class="input sm:w-40">
                <button type="submit" class="btn-secondary">Filter</button>
            </form>
        </x-slot:actions>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Cashier</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Method</th>
                        <th class="text-right">Total</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td class="font-mono text-xs font-semibold text-brand-700">{{ $sale->invoice_number }}</td>
                            <td>{{ $sale->sold_at?->format('d M Y H:i') }}</td>
                            <td>{{ $sale->user?->name }}</td>
                            <td>{{ $sale->customer?->full_name ?? 'Walk-in' }}</td>
                            <td class="text-center">{{ $sale->items->sum('quantity') }}</td>
                            <td class="uppercase text-xs text-gray-500">{{ $sale->payment_method }}</td>
                            <td class="text-right font-bold">{{ number_format($sale->total, 2) }}</td>
                            <td>
                                @if ($sale->isCompleted())
                                    <x-badge color="green">Completed</x-badge>
                                @else
                                    <x-badge color="red">Cancelled</x-badge>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('sales.show', $sale) }}" class="btn-ghost px-2 py-1">View</a>
                                    <a href="{{ route('sales.receipt', $sale) }}" class="btn-ghost px-2 py-1">Receipt</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-empty-state title="No sales found" description="Sales will appear here once they are completed at the POS.">
                                    <x-slot:action>
                                        @can('create sales')
                                            <x-button href="{{ route('pos.index') }}">Start Selling</x-button>
                                        @endcan
                                    </x-slot:action>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 p-4">
            {{ $sales->links() }}
        </div>
    </x-card>
@endsection