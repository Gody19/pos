@extends('layouts.app')

@section('title', 'Customers')

@section('content')
    <x-page-header title="Customers" description="Manage your customers and loyalty points.">
        <x-slot:actions>
            @can('create customers')
                <x-button href="{{ route('customers.create') }}">Add Customer</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <x-slot:actions>
            <form method="GET">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, email, code…"
                    class="input sm:w-72" autocomplete="off">
                <button type="submit" class="btn-secondary">Search</button>
            </form>
        </x-slot:actions>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Code</th>
                        <th>Contact</th>
                        <th>Loyalty Points</th>
                        <th class="text-right">Total Spent</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="font-semibold text-gray-900">{{ $customer->full_name }}</td>
                            <td class="text-xs text-gray-400">{{ $customer->customer_code }}</td>
                            <td>
                                <p class="text-sm text-gray-600">{{ $customer->phone }}</p>
                                <p class="text-xs text-gray-400">{{ $customer->email }}</p>
                            </td>
                            <td>
                                <x-badge color="purple">{{ number_format($customer->loyalty_points) }} pts</x-badge>
                            </td>
                            <td class="text-right font-semibold">{{ number_format($customer->sales_sum_total ?? 0, 2) }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('customers.show', $customer) }}" class="btn-ghost px-2 py-1">View</a>
                                    @can('update customers')
                                        <a href="{{ route('customers.edit', $customer) }}" class="btn-ghost px-2 py-1">Edit</a>
                                    @endcan
                                    @can('delete customers')
                                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" data-confirm="Delete this customer?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost px-2 py-1 text-red-600 hover:bg-red-50">Delete</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="No customers found" description="Add customers to track loyalty points and purchase history.">
                                    <x-slot:action>
                                        @can('create customers')
                                            <x-button href="{{ route('customers.create') }}">Add Customer</x-button>
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
            {{ $customers->links() }}
        </div>
    </x-card>
@endsection