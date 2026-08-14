@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-header title="Dashboard" description="Overview of your store's performance today." />

    @if ($myShift)
        <div class="mb-6 flex flex-col items-start justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 sm:flex-row sm:items-center">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-emerald-800">Your shift is open</p>
                    <p class="text-xs text-emerald-700">Opened at {{ $myShift->opened_at->format('H:i') }} · Opening balance {{ number_format($myShift->opening_balance, 0) }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <x-button variant="secondary" href="{{ route('pos.index') }}">New Sale</x-button>
                <x-button variant="danger" href="{{ route('shifts.close') }}">Close Shift</x-button>
            </div>
        </div>
    @endif

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Today's Sales" value="{{ number_format($todayRevenue, 2) }}" color="brand"
            sub="{{ $todayOrders }} order(s)" :icon="'<svg class=\'h-6 w-6\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z\' /></svg>'" />
        <x-stat-card label="Today's Profit" value="{{ number_format($todayProfit, 2) }}" color="green"
            sub="{{ number_format($todayRevenue - $todayProfit, 2) }} cost" :icon="'<svg class=\'h-6 w-6\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3 17l6-6 4 4 8-8m0 0h-5m5 0v5\' /></svg>'" />
        <x-stat-card label="Open Shifts" value="{{ $openShifts }}" color="blue"
            sub="{{ auth()->user()->activeShift() ? 'You have an open shift' : 'No active shift' }}" :icon="'<svg class=\'h-6 w-6\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z\' /></svg>'" />
        <x-stat-card label="Low Stock Items" value="{{ $lowStock }}" color="red"
            sub="{{ number_format($totalRevenue, 0) }} total revenue" :icon="'<svg class=\'h-6 w-6\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z\' /></svg>'" />
    </div>

    {{-- Charts --}}
    <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-card title="Sales Trend (14 days)" class="xl:col-span-2">
            <canvas id="trendChart" height="120"></canvas>
        </x-card>

        <x-card title="Payment Methods (today)">
            <canvas id="paymentChart" height="120"></canvas>
        </x-card>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-card title="Hourly Sales (today)">
            <canvas id="hourlyChart" height="160"></canvas>
        </x-card>

        <x-card title="Top Products (14 days)">
            <div class="space-y-3">
                @forelse ($topProducts as $product)
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-800">{{ $product->name }}</p>
                            <p class="text-xs text-gray-400">{{ number_format($product->total_qty) }} sold</p>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($product->revenue, 2) }}</span>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-gray-400">No product sales yet.</p>
                @endforelse
            </div>
        </x-card>
    </div>

    <div class="mt-4">
        <x-card title="Low Stock Alerts">
            <x-slot:actions>
                @if (auth()->user()->can('manage inventory'))
                    <a href="{{ route('inventory.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View inventory →</a>
                @endif
            </x-slot:actions>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @forelse ($lowStockItems as $item)
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 p-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-amber-800">{{ $item->product?->name }}</p>
                            <p class="text-xs text-amber-700">SKU: {{ $item->product?->sku }}</p>
                        </div>
                        <x-badge color="red">{{ $item->quantity }} left</x-badge>
                    </div>
                @empty
                    <p class="col-span-full py-8 text-center text-sm text-gray-400">No low stock items.</p>
                @endforelse
            </div>
        </x-card>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const trendData = @json($trend->pluck('revenue')->values());
        const trendLabels = @json($trend->pluck('date')->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M')));

        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Revenue',
                    data: trendData,
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.1)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
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

        const paymentData = @json($paymentMix->pluck('amount')->values());
        const paymentLabels = @json($paymentMix->pluck('payment_method')->map(fn ($m) => ucfirst($m)));

        new Chart(document.getElementById('paymentChart'), {
            type: 'doughnut',
            data: {
                labels: paymentLabels,
                datasets: [{
                    data: paymentData,
                    backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });

        const hourly = @json($hourly);
        const hours = Array.from({ length: 24 }, (_, i) => `${i}:00`);
        const hourlyRevenue = hours.map((_, i) => {
            const row = hourly.find((h) => h.hour === i);
            return row ? Number(row.revenue) : 0;
        });

        new Chart(document.getElementById('hourlyChart'), {
            type: 'bar',
            data: {
                labels: hours,
                datasets: [{
                    label: 'Revenue',
                    data: hourlyRevenue,
                    backgroundColor: '#10b981',
                    borderRadius: 3,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { ticks: { callback: (v) => window.formatMoney(v) } },
                    x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } },
                },
            },
        });
    });
</script>
@endpush