@forelse ($customers as $customer)
    <button type="button" data-customer="{{ $customer->id }}" data-customer-name="{{ $customer->full_name }}"
        class="flex w-full items-center justify-between gap-2 px-3 py-2.5 text-left hover:bg-gray-50">
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-gray-800">{{ $customer->full_name }}</p>
            <p class="truncate text-xs text-gray-400">{{ $customer->phone ?? $customer->email ?? $customer->customer_code }}</p>
        </div>
        <span class="badge bg-brand-50 text-brand-700">{{ $customer->loyalty_points }} pts</span>
    </button>
@empty
    <p class="px-3 py-4 text-center text-sm text-gray-400">No customers found.</p>
@endforelse