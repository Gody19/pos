@extends('layouts.app')

@section('title', 'Daily Sales Report')

@section('content')
    <x-page-header title="Daily Sales Report" description="Revenue broken down by day." :crumbs="['Reports' => null, 'Daily Sales' => null]">
        <x-slot:actions>
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input sm:w-44">
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input sm:w-44">
                <button type="submit" class="btn-secondary">Apply</button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-stat-card label="Total Orders" value="{{ number_format($totals['orders']) }}" color="blue" />
        <x-stat-card label="Total Revenue" value="{{ number_format($totals['revenue'], 2) }}" color="brand" />
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-right">Orders</th>
                        <th class="text-right">Gross</th>
                        <th class="text-right">Tax</th>
                        <th class="text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                            <td class="text-right">{{ number_format($row['orders']) }}</td>
                            <td class="text-right">{{ number_format($row['gross'], 2) }}</td>
                            <td class="text-right">{{ number_format($row['tax'], 2) }}</td>
                            <td class="text-right font-bold text-brand-700">{{ number_format($row['revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state title="No sales in this period" description="Try widening the date range." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection