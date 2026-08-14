@forelse ($products as $product)
    <button type="button" data-product-id="{{ $product->id }}"
        class="group flex flex-col rounded-xl border border-gray-200 bg-white p-3 text-left shadow-sm transition hover:border-brand-400 hover:shadow-md">
        <div class="mb-2 flex aspect-square items-center justify-center overflow-hidden rounded-lg bg-gray-100">
            @if ($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}"
                    class="h-full w-full object-cover" loading="lazy">
            @else
                <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            @endif
        </div>
        <p class="truncate text-sm font-semibold text-gray-800 group-hover:text-brand-700">{{ $product->name }}</p>
        <p class="text-xs text-gray-400">{{ $product->sku }}</p>
        <div class="mt-2 flex items-center justify-between">
            <span class="text-sm font-bold text-brand-600">{{ number_format($product->selling_price, 0) }}</span>
            @php $stock = $product->stockQuantity(); @endphp
            @if ($stock <= 0)
                <span class="badge bg-red-100 text-red-700">Out</span>
            @elseif ($product->isLowStock())
                <span class="badge bg-amber-100 text-amber-700">{{ $stock }}</span>
            @else
                <span class="badge bg-emerald-100 text-emerald-700">{{ $stock }}</span>
            @endif
        </div>
    </button>
@empty
    <div class="col-span-full">
        <x-empty-state title="No products found" description="Try a different search term or category.">
            <x-slot:action>
                <button type="button" id="clear-filters-btn" class="btn-secondary">Clear filters</button>
            </x-slot:action>
        </x-empty-state>
    </div>
@endforelse