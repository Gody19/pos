@extends('layouts.app')

@section('title', 'Loyalty History')

@section('content')
    <x-page-header title="Loyalty History" :description="$customer->full_name.' · '.number_format($customer->loyalty_points).' points available'"
        :crumbs="['Customers' => route('customers.index'), $customer->full_name => route('customers.show', $customer), 'Loyalty' => null]" />

    <div class="mx-auto max-w-3xl">
        <x-card title="Points transactions">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice</th>
                            <th>Type</th>
                            <th class="text-right">Points</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $txn)
                            <tr>
                                <td>{{ $txn->created_at->format('d M Y H:i') }}</td>
                                <td class="font-mono text-xs text-gray-500">{{ $txn->sale?->invoice_number ?? '—' }}</td>
                                <td>
                                    @if ($txn->type === 'earned')
                                        <x-badge color="green">Earned</x-badge>
                                    @elseif ($txn->type === 'redeemed')
                                        <x-badge color="red">Redeemed</x-badge>
                                    @else
                                        <x-badge>Adjusted</x-badge>
                                    @endif
                                </td>
                                <td class="text-right font-semibold {{ $txn->type === 'earned' ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $txn->type === 'earned' ? '+' : '-' }}{{ $txn->points }}
                                </td>
                                <td class="text-xs text-gray-500">{{ $txn->notes }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state title="No loyalty transactions" description="Points are earned when this customer makes purchases." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 p-4">
                {{ $transactions->links() }}
            </div>
        </x-card>
    </div>
@endsection