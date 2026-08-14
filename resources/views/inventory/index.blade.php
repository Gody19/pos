@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
    <x-page-header title="Inventory" description="Monitor stock levels and reorder points.">
        <x-slot:actions>
            <a href="{{ route('inventory.movements') }}" class="btn-secondary">View Movements</a>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-stat-card label="Low Stock Items" value="{{ $lowStockCount }}" color="yellow"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z\' /></svg>'" />
        <x-stat-card label="Out of Stock" value="{{ $outOfStockCount }}" color="red"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636\' /></svg>'" />
    </div>

    <x-card title="Stock levels">
        <x-slot:actions>
            <form method="GET" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product or SKU…"
                    class="input sm:w-64" autocomplete="off">
                <select name="filter" class="input sm:w-40" onchange="this.form.submit()">
                    <option value="">All stock</option>
                    <option value="low" @selected(request('filter') === 'low')>Low stock</option>
                    <option value="out" @selected(request('filter') === 'out')>Out of stock</option>
                </select>
                <button type="submit" class="btn-secondary">Filter</button>
            </form>
        </x-slot:actions>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Reorder Level</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td class="font-semibold text-gray-900">{{ $product->name }}</td>
                            <td class="text-xs text-gray-500">{{ $product->sku }}</td>
                            <td class="text-right">
                                <span class="{{ $product->inventory && $product->inventory->quantity <= 0 ? 'font-bold text-red-600' : 'font-semibold' }}">
                                    {{ $product->inventory?->quantity ?? 0 }}
                                </span>
                            </td>
                            <td class="text-right text-gray-500">{{ $product->inventory?->reorder_level ?? '—' }}</td>
                            <td>{{ $product->inventory?->location ?? '—' }}</td>
                            <td>
                                @if ($product->isOutOfStock())
                                    <x-badge color="red">Out of stock</x-badge>
                                @elseif ($product->isLowStock())
                                    <x-badge color="yellow">Low</x-badge>
                                @else
                                    <x-badge color="green">In stock</x-badge>
                                @endif
                            </td>
                            <td>
                                <div class="flex justify-end">
                                    <button type="button" data-adjust="{{ $product->id }}" data-name="{{ $product->name }}"
                                        data-stock="{{ $product->inventory?->quantity ?? 0 }}"
                                        class="btn-ghost px-2 py-1 text-brand-600">Adjust</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state title="No products found" description="Adjust your filters or add products to the catalogue." />
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

    {{-- Adjust stock modal --}}
    <div id="adjust-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" data-close-adjust></div>
        <div class="card relative z-10 w-full max-w-md p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Adjust stock</h3>
                <button type="button" data-close-adjust class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p id="adjust-product" class="mb-4 rounded-lg bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-800"></p>

            <form method="POST" action="{{ route('inventory.adjust') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="product_id" id="adjust-product-id">

                <x-select name="type" label="Adjustment type" :options="['in' => 'Receive stock (in)', 'out' => 'Remove stock (out)', 'adjustment' => 'Set quantity (adjustment)']" selected="in" />
                <x-input name="quantity" label="Quantity" type="number" step="1" min="1" value="1" placeholder="1" />
                <x-textarea name="reason" label="Reason" rows="2" placeholder="e.g. Supplier delivery, damaged goods, stock count…" />

                <div class="flex gap-2">
                    <button type="button" data-close-adjust class="btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="btn-primary flex-1">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const adjustModal = document.getElementById('adjust-modal');
    const closeAdjust = () => {
        adjustModal.classList.add('hidden');
        adjustModal.classList.remove('flex');
    };

    document.querySelectorAll('[data-adjust]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById('adjust-product-id').value = btn.dataset.adjust;
            document.getElementById('adjust-product').textContent = btn.dataset.name + ' · Current stock: ' + btn.dataset.stock;
            adjustModal.classList.remove('hidden');
            adjustModal.classList.add('flex');
        });
    });

    document.querySelectorAll('[data-close-adjust]').forEach((el) => el.addEventListener('click', closeAdjust));
</script>
@endpush