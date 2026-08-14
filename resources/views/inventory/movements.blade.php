@extends('layouts.app')

@section('title', 'Inventory Movements')

@section('content')
    <x-page-header title="Inventory Movements" description="A log of every stock change in the system.">
        <x-slot:actions>
            <a href="{{ route('inventory.index') }}" class="btn-secondary">Back to Inventory</a>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <x-slot:actions>
            <form method="GET">
                <select name="type" class="input sm:w-48" onchange="this.form.submit()">
                    <option value="">All types</option>
                    <option value="in" @selected(request('type') === 'in')>In</option>
                    <option value="out" @selected(request('type') === 'out')>Out</option>
                    <option value="sale" @selected(request('type') === 'sale')>Sale</option>
                    <option value="adjustment" @selected(request('type') === 'adjustment')>Adjustment</option>
                </select>
            </form>
        </x-slot:actions>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th class="text-right">Quantity</th>
                        <th>Reference</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td>{{ $movement->created_at->format('d M Y H:i') }}</td>
                            <td class="font-semibold text-gray-900">{{ $movement->product?->name ?? 'Unknown' }}</td>
                            <td>
                                @if ($movement->type === 'in')
                                    <x-badge color="green">In</x-badge>
                                @elseif ($movement->type === 'out')
                                    <x-badge color="yellow">Out</x-badge>
                                @elseif ($movement->type === 'sale')
                                    <x-badge color="blue">Sale</x-badge>
                                @else
                                    <x-badge color="gray">Adjustment</x-badge>
                                @endif
                            </td>
                            <td class="text-right font-semibold {{ $movement->type === 'in' ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $movement->type === 'in' ? '+' : '-' }}{{ $movement->quantity }}
                            </td>
                            <td class="font-mono text-xs text-gray-500">{{ $movement->reference_type }}#{{ $movement->reference_id }}</td>
                            <td class="text-xs text-gray-500">{{ $movement->notes }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="No movements recorded" description="Stock movements appear when sales are made or stock is adjusted." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 p-4">
            {{ $movements->links() }}
        </div>
    </x-card>
@endsection