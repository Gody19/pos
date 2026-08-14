@props(['product' => null, 'categories' => [], 'routeName', 'submitLabel'])

@php
    $inventory = $product?->inventory;
@endphp

<form method="POST" action="{{ $routeName }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($product)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <x-card title="Product details">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input name="name" label="Product name" value="{{ $product?->name }}" placeholder="e.g. Coca Cola 500ml" autofocus />
                    </div>
                    <div>
                        <x-select name="category_id" label="Category" :options="$categories->pluck('name', 'id')" selected="{{ $product?->category_id }}" placeholder="Select a category" />
                    </div>
                    <div>
                        <x-input name="sku" label="SKU" value="{{ $product?->sku }}" placeholder="e.g. SKU-000001" />
                    </div>
                    <div>
                        <x-input name="barcode" label="Barcode" value="{{ $product?->barcode }}" placeholder="Scan or type barcode" />
                    </div>
                    <div>
                        <x-input name="tax_rate" label="Tax rate (%)" type="number" min="0" max="100" step="0.01" value="{{ $product?->tax_rate ?? 0 }}" placeholder="0" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-textarea name="description" label="Description" rows="3" value="{{ $product?->description }}" placeholder="Optional description…" />
                    </div>
                </div>
            </x-card>

            <x-card title="Pricing">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-input name="cost_price" label="Cost price" type="number" min="0" step="0.01" value="{{ $product?->cost_price ?? 0 }}" placeholder="0.00" />
                    <x-input name="selling_price" label="Selling price" type="number" min="0" step="0.01" value="{{ $product?->selling_price ?? 0 }}" placeholder="0.00" />
                </div>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Stock">
                <div class="space-y-4">
                    <x-input name="quantity" label="Quantity on hand" type="number" min="0" step="1" value="{{ $inventory?->quantity ?? 0 }}" />
                    <x-input name="reorder_level" label="Reorder level" type="number" min="0" step="1" value="{{ $inventory?->reorder_level ?? 5 }}" hint="Alert when stock drops to or below this level." />
                    <x-input name="location" label="Storage location" value="{{ $inventory?->location }}" placeholder="e.g. Aisle A1" />
                </div>
            </x-card>

            <x-card title="Image & status">
                <div class="space-y-4">
                    <div>
                        <label class="label">Product image</label>
                        @if ($product?->image)
                            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="mb-2 h-32 w-32 rounded-lg object-cover">
                        @endif
                        <input type="file" name="image" accept="image/*" class="input file:border-0 file:bg-gray-100 file:py-2 file:px-3 file:rounded-lg file:mr-3">
                        @error('image')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                            <input type="checkbox" name="status" value="1"
                                @checked((bool) ($product?->status ?? true))
                                class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            Active (available for sale)
                        </label>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <div class="flex items-center justify-end gap-2">
        <x-button variant="secondary" href="{{ route('products.index') }}">Cancel</x-button>
        <button type="submit" class="btn-primary">{{ $submitLabel }}</button>
    </div>
</form>