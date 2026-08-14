@extends('layouts.app')

@section('title', 'Shift History')

@section('content')
    <x-page-header title="Shift History" description="All shifts opened and closed on this account." />

    <x-card>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Opened</th>
                        <th>Closed</th>
                        <th>Opening</th>
                        <th>Sales</th>
                        <th>Expected</th>
                        <th>Counted</th>
                        <th>Difference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shifts as $shift)
                        <tr>
                            <td>{{ $shift->opened_at?->format('d M Y H:i') }}</td>
                            <td>{{ $shift->closed_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td>{{ number_format($shift->opening_balance, 2) }}</td>
                            <td>
                                {{ $shift->sales_count }} · {{ number_format($shift->totalSales(), 2) }}
                            </td>
                            <td class="font-semibold">{{ number_format($shift->expected_balance, 2) }}</td>
                            <td>{{ number_format($shift->closing_balance, 2) }}</td>
                            <td>
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
                            <td colspan="8">
                                <x-empty-state title="No shifts yet" description="Open your first shift to start recording sales." />
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