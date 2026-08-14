@extends('layouts.app')

@section('title', 'Cashier Performance')

@section('content')
    <x-page-header title="Cashier Performance" description="Compare sales made by each cashier." :crumbs="['Reports' => null, 'Cashier Performance' => null]">
        <x-slot:actions>
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input sm:w-44">
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input sm:w-44">
                <button type="submit" class="btn-secondary">Apply</button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Revenue by cashier">
            <canvas id="cashierChart" height="160"></canvas>
        </x-card>

        <x-card title="Details">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Cashier</th>
                            <th class="text-right">Orders</th>
                            <th class="text-right">Revenue</th>
                            <th class="text-right">Avg / Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td class="font-semibold text-gray-900">{{ $row['cashier'] }}</td>
                                <td class="text-right">{{ number_format($row['orders']) }}</td>
                                <td class="text-right font-bold text-brand-700">{{ number_format($row['revenue'], 2) }}</td>
                                <td class="text-right">{{ number_format($row['avg'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-empty-state title="No sales in this period" description="Try widening the date range." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const rows = @json($rows);
        new Chart(document.getElementById('cashierChart'), {
            type: 'bar',
            data: {
                labels: rows.map((r) => r.cashier),
                datasets: [{
                    label: 'Revenue',
                    data: rows.map((r) => r.revenue),
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { callback: (v) => window.formatMoney(v) } },
                },
            },
        });
    });
</script>
@endpush