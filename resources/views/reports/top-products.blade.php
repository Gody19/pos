@extends('layouts.app')

@section('title', 'Top Products')

@section('content')
    <x-page-header title="Top Products" description="Best selling products by quantity and revenue." :crumbs="['Reports' => null, 'Top Products' => null]">
        <x-slot:actions>
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input sm:w-44">
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input sm:w-44">
                <select name="limit" class="input sm:w-28" onchange="this.form.submit()">
                    @foreach ([5, 10, 25, 50] as $l)
                        <option value="{{ $l }}" @selected((int) request('limit', 10) === $l)>{{ $l }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary">Apply</button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th class="text-right">Quantity Sold</th>
                        <th class="text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $index => $row)
                        <tr>
                            <td class="text-gray-400">{{ $index + 1 }}</td>
                            <td class="font-semibold text-gray-900">{{ $row->name }}</td>
                            <td class="text-xs text-gray-500">{{ $row->sku }}</td>
                            <td class="text-right">
                                <x-badge color="blue">{{ number_format($row->total_qty) }}</x-badge>
                            </td>
                            <td class="text-right font-bold text-brand-700">{{ number_format($row->revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state title="No product sales in this period" description="Try widening the date range." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection