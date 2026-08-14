@extends('layouts.app')

@section('title', 'Open Shift')

@section('content')
    <x-page-header title="Open Shift" description="Start your cash register shift before processing sales." />

    @if ($openShift)
        <div class="mx-auto max-w-lg">
            <div class="card p-6 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Shift already open</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Your shift opened at <span class="font-semibold">{{ $openShift->opened_at->format('H:i') }}</span> with
                    opening balance <span class="font-semibold">{{ number_format($openShift->opening_balance, 0) }}</span>.
                </p>
                <div class="mt-6 flex justify-center gap-2">
                    <x-button href="{{ route('pos.index') }}">Go to POS</x-button>
                    <x-button variant="danger" href="{{ route('shifts.close') }}">Close shift</x-button>
                </div>
            </div>
        </div>
    @else
        <div class="mx-auto max-w-lg">
            <x-card title="Opening balance">
                <x-slot:subtitle>Enter the cash amount in the register at the start of your shift.</x-slot:subtitle>
                <form method="POST" action="{{ route('shifts.open.store') }}" class="space-y-4">
                    @csrf

                    <x-input
                        name="opening_balance"
                        label="Cash in drawer"
                        type="number"
                        min="0"
                        step="0.01"
                        inputmode="decimal"
                        placeholder="0.00"
                        value="{{ old('opening_balance') }}"
                        autofocus
                    />

                    <x-textarea name="notes" label="Notes (optional)" rows="3" placeholder="Any notes for this shift…" />

                    <button type="submit" class="btn-primary w-full py-2.5">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Open Shift
                    </button>
                </form>
            </x-card>
        </div>
    @endif
@endsection