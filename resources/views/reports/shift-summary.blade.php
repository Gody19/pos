@extends('layouts.app')

@section('title', 'Shift Summary')

@section('content')
    <x-page-header title="Shift Summary" description="Reconciled shift records across all cashiers." :crumbs="['Reports' => null, 'Shift Summary' => null]">
        <x-slot:actions>
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input sm:w-44">
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input sm:w-44">
                <button type="submit" class="btn-secondary">Apply</button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Cashier</th>
                        <th>Opened</th>
                        <th>Closed</th>
                        <th class="text-right">Opening</th>
                        <th class="text-right">Sales</th>
                        <th class="text-right">Expected</th>
                        <th class="text-right">Counted</th>
                        <th class="text-right">Difference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shifts as $shift)
                        <tr>
                            <td class="font-semibold text-gray-900">{{ $shift->user?->name }}</td>
                            <td>{{ $shift->opened_at?->format('d M Y H:i') }}</td>
                            <td>{{ $shift->closed_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td class="text-right">{{ number_format($shift->opening_balance, 2) }}</td>
                            <td class="text-right">{{ number_format($shift->totalSales(), 2) }}</td>
                            <td class="text-right font-semibold">{{ number_format($shift->expected_balance, 2) }}</td>
                            <td class="text-right">{{ number_format($shift->closing_balance, 2) }}</td>
                            <td class="text-right">
                                @if ($shift->expected_balance !== null && $shift->closing_balance !== null)
                                    @php $diff = $shift->closing_balance - $shift->expected_balance; @endphp
                                    <span class="{{ $diff >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($shift->isOpen())
                                    <x-badge color="green">Open</x-badge>
                                @else
                                    <x-badge>Closed</x-badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-empty-state title="No shifts in this period" description="Try widening the date range." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 p-4">
            {{ $shifts->links() }}
        </div>
    </x-card>
@endsection