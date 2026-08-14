@extends('layouts.app')

@section('title', 'Monthly Sales Report')

@section('content')
    <x-page-header title="Monthly Sales Report" description="Revenue aggregated by month for a given year." :crumbs="['Reports' => null, 'Monthly Sales' => null]">
        <x-slot:actions>
            <form method="GET">
                <select name="year" class="input sm:w-36" onchange="this.form.submit()">
                    @for ($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-stat-card label="Orders ({{ $year }})" value="{{ number_format($totals['orders']) }}" color="blue" />
        <x-stat-card label="Revenue ({{ $year }})" value="{{ number_format($totals['revenue'], 2) }}" color="brand" />
    </div>

    <x-card title="Monthly totals">
        <canvas id="monthlyChart" height="110"></canvas>
    </x-card>

    <div class="mt-4">
        <x-card>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-right">Orders</th>
                            <th class="text-right">Tax</th>
                            <th class="text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td class="font-semibold text-gray-900">{{ \Carbon\Carbon::create()->month($row['month'])->format('F') }}</td>
                                <td class="text-right">{{ number_format($row['orders']) }}</td>
                                <td class="text-right">{{ number_format($row['tax'], 2) }}</td>
                                <td class="text-right font-bold text-brand-700">{{ number_format($row['revenue'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-empty-state title="No sales in {{ $year }}" description="No completed sales were recorded this year." />
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
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const labels = Array.from({ length: 12 }, (_, i) => monthNames[i]);
        const rows = @json($rows);
        const data = labels.map((_, i) => {
            const row = rows.find((r) => r.month === i + 1);
            return row ? Number(row.revenue) : 0;
        });

        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Revenue',
                    data,
                    backgroundColor: '#059669',
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { ticks: { callback: (v) => window.formatMoney(v) } },
                },
            },
        });
    });
</script>
@endpush