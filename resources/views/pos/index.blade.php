@extends('layouts.app')

@section('title', 'POS Terminal')

@section('content')
<div data-pos-page class="flex h-[calc(100vh-9rem)] flex-col gap-4 xl:flex-row">
    {{-- Left: catalogue --}}
    <div class="flex min-w-0 flex-1 flex-col card overflow-hidden">
        {{-- Search & filters --}}
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959m0 0a2.25 2.25 0 01-2.25 2.25c-.966 0-1.75-.756-1.75-1.689l-4.9-2.238a2.25 2.25 0 10-1.742 4.153l4.9 2.238a2.25 2.25 0 101.742-4.153m-2.25 8.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5zm8.5-2.25a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM19 21a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <input type="text" id="barcode-input" placeholder="Scan barcode, then press Enter…"
                    class="input pl-10 font-mono" autocomplete="off" autofocus>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="pos-search" placeholder="Search products, SKU or barcode…"
                        class="input pl-10" autocomplete="off">
                </div>
                <select id="category-filter" class="input sm:w-56">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Product grid --}}
        <div id="product-grid" class="scrollbar-thin grid flex-1 grid-cols-2 gap-3 overflow-y-auto p-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
            @include('pos._products', ['products' => $products])
        </div>
    </div>

    {{-- Right: cart --}}
    <div id="cart-panel" class="card flex w-full flex-col overflow-hidden xl:w-96 xl:shrink-0" data-count="{{ $cart['count'] }}">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <h2 class="text-base font-bold text-gray-900">Current Sale</h2>
            <span data-cart-count class="hidden rounded-full bg-brand-600 px-2.5 py-0.5 text-xs font-bold text-white">{{ $cart['count'] }}</span>
        </div>

        {{-- Customer selector --}}
        <div class="border-b border-gray-100 p-4">
            <label for="customer-search" class="label">Customer</label>
            <div class="relative">
                <input type="text" id="customer-search" placeholder="Search customer…"
                    class="input" autocomplete="off">
                <div id="customer-results" class="absolute z-10 mt-1 hidden w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"></div>
            </div>
            <p class="mt-1 text-xs text-gray-400">Leave blank for walk-in customers.</p>
        </div>

        {{-- Cart items --}}
        <div id="cart-items" class="scrollbar-thin flex-1 overflow-y-auto">
            @if (empty($cart['cart']))
                <div class="py-16 text-center text-gray-400">
                    <p class="mb-2 text-4xl">🛒</p>
                    <p class="text-sm">Cart is empty</p>
                    <p class="mt-1 text-xs">Scan or search a product to begin</p>
                </div>
            @else
                @foreach ($cart['cart'] as $item)
                    <div class="flex flex-col gap-2 border-b border-gray-100 px-4 py-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-800">{{ $item['name'] }}</p>
                                <p class="text-xs text-gray-400">{{ number_format($item['price'], 2) }} × {{ $item['quantity'] }}</p>
                            </div>
                            <button type="button" class="text-gray-300 hover:text-red-500" data-remove="{{ $item['product_id'] }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <button type="button" data-qty="{{ $item['product_id'] }}" data-delta="-1"
                                    class="h-7 w-7 rounded-md border border-gray-200 text-gray-500 hover:bg-gray-100">−</button>
                                <span class="w-8 text-center text-sm font-semibold">{{ $item['quantity'] }}</span>
                                <button type="button" data-qty="{{ $item['product_id'] }}" data-delta="1"
                                    class="h-7 w-7 rounded-md border border-gray-200 text-gray-500 hover:bg-gray-100">+</button>
                            </div>
                            <span class="text-sm font-bold text-gray-900">{{ number_format($item['total'], 2) }}</span>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Totals --}}
        <div class="space-y-2 border-t border-gray-100 bg-gray-50 p-4">
            <div class="flex items-center justify-between text-sm text-gray-600">
                <span>Subtotal</span>
                <span id="totals-subtotal" class="font-semibold">{{ number_format($cart['subtotal'], 2) }}</span>
            </div>
            <div class="flex items-center justify-between text-sm text-gray-600">
                <span>Tax</span>
                <span id="totals-tax" class="font-semibold">{{ number_format($cart['tax'], 2) }}</span>
            </div>
            <div class="flex items-center justify-between gap-2">
                <span class="text-sm text-gray-600">Discount</span>
                <input type="number" id="cart-discount" value="{{ number_format($cart['discount'], 2) }}"
                    min="0" step="0.01" inputmode="decimal" placeholder="0.00"
                    class="input h-8 w-24 text-right text-sm">
            </div>
            <div class="flex items-center justify-between border-t border-dashed border-gray-200 pt-2">
                <span class="text-base font-bold text-gray-900">Total</span>
                <span id="totals-total" class="text-xl font-bold text-brand-600">{{ number_format($cart['total'], 2) }}</span>
            </div>

            <form id="cart-form" method="POST" action="{{ route('pos.index') }}">
                @csrf
                <input type="hidden" name="customer_id" id="customer-id" value="{{ $cart['customer_id'] }}">
                <x-textarea name="note" label="Order note (optional)" rows="2" placeholder="Any notes about this order…" :value="$cart['notes']" class="mt-2" />
            </form>

            <button type="button" id="checkout-btn" {{ empty($cart['cart']) ? 'disabled' : '' }}
                class="btn-success w-full py-3 text-base {{ empty($cart['cart']) ? 'opacity-50 cursor-not-allowed' : '' }}">
                Checkout · <span id="checkout-total">{{ number_format($cart['total'], 2) }}</span>
            </button>
        </div>
    </div>
</div>

{{-- Payment modal --}}
<div id="payment-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
    <div class="card relative z-10 w-full max-w-lg p-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Take payment</h3>
            <button type="button" data-close-payment class="text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="mb-4 flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3">
            <span class="text-sm font-medium text-gray-600">Sale total</span>
            <span class="text-xl font-bold text-gray-900">{{ number_format($cart['total'], 2) }}</span>
        </div>

        <div id="payment-methods" class="space-y-2"></div>

        <div class="mt-4 flex items-center justify-between">
            <button type="button" id="add-payment-row" class="text-sm font-medium text-brand-600 hover:text-brand-700">
                + Add payment method
            </button>
            <p class="text-sm text-gray-600">
                Remaining: <span id="pay-remaining" class="font-bold">0.00</span>
            </p>
        </div>

        <div class="mt-6 flex gap-2">
            <button type="button" data-close-payment class="btn-secondary flex-1">Cancel</button>
            <button type="button" id="complete-sale-btn" class="btn-success flex-1 py-3">Complete Sale</button>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    #customer-results:not(:empty) { display: block; }
</style>
@endsection

@push('scripts')
@vite('resources/js/pos.js')
<script>
    const modal = document.getElementById('payment-modal');
    document.querySelectorAll('[data-close-payment]').forEach((btn) => {
        btn.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
    });
</script>
@endpush