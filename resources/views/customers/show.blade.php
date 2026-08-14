@extends('layouts.app')

@section('title', $customer->full_name)

@section('content')
    <x-page-header :title="$customer->full_name" description="Customer profile and purchase history." :crumbs="['Customers' => route('customers.index'), $customer->full_name => null]">
        <x-slot:actions>
            @can('update customers')
                <x-button variant="secondary" href="{{ route('customers.edit', $customer) }}">Edit Customer</x-button>
            @endcan
            @can('create sales')
                <x-button href="{{ route('pos.index') }}">New Sale</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <x-card title="Profile">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Code</dt>
                        <dd class="font-semibold text-gray-900">{{ $customer->customer_code }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="text-gray-900">{{ $customer->phone ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="text-gray-900">{{ $customer->email ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Address</dt>
                        <dd class="text-right text-gray-900">{{ $customer->address ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-3">
                        <dt class="text-gray-500">Member since</dt>
                        <dd class="text-gray-900">{{ $customer->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>
            </x-card>

            <x-card title="Loyalty">
                <div class="text-center">
                    <p class="text-4xl font-bold text-brand-600">{{ number_format($customer->loyalty_points) }}</p>
                    <p class="mt-1 text-sm text-gray-500">points available</p>
                    <p class="mt-3 text-xs text-gray-400">1 point per {{ number_format(config('pos.loyalty.points_per_currency', 1000)) }} spent</p>
                </div>
                <div class="mt-4">
                    <a href="{{ route('customers.loyalty', $customer) }}" class="btn-secondary w-full">View loyalty history</a>
                </div>
            </x-card>

            <x-card title="Summary">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Total spent</dt>
                        <dd class="font-bold text-gray-900">{{ number_format($totalSpent, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Orders</dt>
                        <dd class="font-semibold text-gray-900">{{ $customer->sales->count() }}</dd>
                    </div>
                </dl>
            </x-card>
        </div>

        <div class="lg:col-span-2">
            <x-card title="Recent purchases">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Date</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customer->sales as $sale)
                                <tr>
                                    <td class="font-mono text-xs text-gray-600">{{ $sale->invoice_number }}</td>
                                    <td>{{ $sale->sold_at?->format('d M Y H:i') }}</td>
                                    <td class="text-right font-semibold">{{ number_format($sale->total, 2) }}</td>
                                    <td class="text-right">
                                        @if ($sale->isCompleted())
                                            <x-badge color="green">Completed</x-badge>
                                        @else
                                            <x-badge color="red">Cancelled</x-badge>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('sales.show', $sale) }}" class="btn-ghost px-2 py-1">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-empty-state title="No purchases yet" description="This customer hasn't made any purchases." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection