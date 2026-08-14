@extends('layouts.app')

@section('title', 'Close Shift')

@section('content')
    <x-page-header title="Close Shift" description="Reconcile your cash drawer and close the current shift." />

    @if ($shift === null)
        <div class="mx-auto max-w-lg">
            <div class="card p-10 text-center">
                <x-empty-state title="No open shift" description="There is no open shift to close. Open a shift first to start selling.">
                    <x-slot:action>
                        <x-button href="{{ route('shifts.open') }}">Open a shift</x-button>
                    </x-slot:action>
                </x-empty-state>
            </div>
            @if ($history->isNotEmpty())
                <div class="mt-6">
                    <x-card title="Recent shifts">
                        <div class="divide-y divide-gray-100">
                            @foreach ($history as $s)
                                <div class="flex items-center justify-between py-2 text-sm">
                                    <span class="text-gray-600">{{ $s->opened_at->format('d M Y H:i') }}</span>
                                    <span class="font-semibold text-gray-900">{{ number_format($s->totalSales(), 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                </div>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-card title="Shift summary">
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Opened at</span>
                        <span class="font-semibold text-gray-900">{{ $shift->opened_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Opening balance</span>
                        <span class="font-semibold text-gray-900">{{ number_format($shift->opening_balance, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Sales count</span>
                        <span class="font-semibold text-gray-900">{{ $shift->sales_count }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Cash sales</span>
                        <span class="font-semibold text-gray-900">{{ number_format($shift->cash_sales, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Card sales</span>
                        <span class="font-semibold text-gray-900">{{ number_format($shift->card_sales, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Mobile sales</span>
                        <span class="font-semibold text-gray-900">{{ number_format($shift->mobile_sales, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">QR sales</span>
                        <span class="font-semibold text-gray-900">{{ number_format($shift->qr_sales, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-3">
                        <span class="text-sm font-bold text-gray-900">Expected in drawer</span>
                        <span class="text-lg font-bold text-brand-600">{{ number_format($shift->opening_balance + $shift->totalSales(), 2) }}</span>
                    </div>
                </div>
            </x-card>

            <x-card title="Count the drawer">
                <x-slot:subtitle>Enter the actual cash counted. The difference will be recorded.</x-slot:subtitle>
                <form method="POST" action="{{ route('shifts.close.store') }}" class="space-y-4">
                    @csrf

                    <x-input
                        name="closing_balance"
                        label="Cash counted in drawer"
                        type="number"
                        min="0"
                        step="0.01"
                        inputmode="decimal"
                        placeholder="0.00"
                        value="{{ old('closing_balance', $shift->opening_balance + $shift->totalSales()) }}"
                        autofocus
                    />

                    <x-textarea name="notes" label="Notes (optional)" rows="3" placeholder="Discrepancies, cash drops, etc." />

                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-3 text-sm text-amber-800">
                        Closing the shift will prevent new sales until you open a new one.
                    </div>

                    <button type="submit" class="btn-danger w-full py-2.5" data-confirm="Close this shift and reconcile the drawer?">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Close Shift
                    </button>
                </form>
            </x-card>
        </div>
    @endif
@endsection