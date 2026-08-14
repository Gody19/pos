@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <x-page-header title="Products" description="Manage your product catalogue and pricing.">
        <x-slot:actions>
            @can('create products')
                <x-button href="{{ route('products.create') }}">Add Product</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <x-slot:actions>
            <form method="GET" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, SKU, barcode…"
                    class="input sm:w-64" autocomplete="off">
                <select name="category_id" class="input sm:w-48" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <select name="status" class="input sm:w-36" onchange="this.form.submit()">
                    <option value="">Any status</option>
                    <option value="1" @selected(request('status') === '1')>Active</option>
                    <option value="0" @selected(request('status') === '0')>Inactive</option>
                </select>
                <button type="submit" class="btn-secondary">Filter</button>
            </form>
        </x-slot:actions>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>SKU / Barcode</th>
                        <th class="text-right">Cost</th>
                        <th class="text-right">Selling</th>
                        <th class="text-right">Stock</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gray-100">
                                        @if ($product->image)
                                            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-xs font-bold text-gray-400">{{ strtoupper(substr($product->name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-gray-900">{{ $product->name }}</p>
                                        <p class="truncate text-xs text-gray-400">{{ Str::limit($product->description, 40) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->category?->name }}</td>
                            <td>
                                <p class="text-xs text-gray-500">{{ $product->sku }}</p>
                                <p class="text-xs text-gray-400">{{ $product->barcode }}</p>
                            </td>
                            <td class="text-right">{{ number_format($product->cost_price, 2) }}</td>
                            <td class="text-right font-semibold">{{ number_format($product->selling_price, 2) }}</td>
                            <td class="text-right">
                                @php $stock = $product->stockQuantity(); @endphp
                                @if ($stock <= 0)
                                    <x-badge color="red">Out</x-badge>
                                @elseif ($product->isLowStock())
                                    <x-badge color="yellow">{{ $stock }}</x-badge>
                                @else
                                    <x-badge color="green">{{ $stock }}</x-badge>
                                @endif
                            </td>
                            <td>
                                @if ($product->status)
                                    <x-badge color="green">Active</x-badge>
                                @else
                                    <x-badge color="gray">Inactive</x-badge>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    @can('update products')
                                        <a href="{{ route('products.edit', $product) }}" class="btn-ghost px-2 py-1">Edit</a>
                                    @endcan
                                    @can('delete products')
                                        <form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm="Delete this product? This cannot be undone.">
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
                            <td colspan="8">
                                <x-empty-state title="No products found" description="Try adjusting your filters or add a new product.">
                                    <x-slot:action>
                                        @can('create products')
                                            <x-button href="{{ route('products.create') }}">Add Product</x-button>
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
            {{ $products->links() }}
        </div>
    </x-card>
@endsection