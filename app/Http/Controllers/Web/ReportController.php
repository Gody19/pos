<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function dailySales(Request $request): View
    {
        $from = Carbon::parse($request->get('from', now()->startOfMonth()))->startOfDay();
        $to = Carbon::parse($request->get('to', now()))->endOfDay();

        $rows = Sale::query()
            ->where('payment_status', 'completed')
            ->whereBetween('sold_at', [$from, $to])
            ->selectRaw('DATE(sold_at) as date, COUNT(*) as orders, SUM(total) as revenue, SUM(subtotal - discount) as gross, SUM(tax) as tax')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($r) => [
                'date' => $r->date,
                'orders' => (int) $r->orders,
                'revenue' => round((float) $r->revenue, 2),
                'gross' => round((float) $r->gross, 2),
                'tax' => round((float) $r->tax, 2),
            ]);

        return view('reports.daily-sales', [
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'totals' => [
                'orders' => $rows->sum('orders'),
                'revenue' => round($rows->sum('revenue'), 2),
            ],
        ]);
    }

    public function monthlySales(Request $request): View
    {
        $year = $request->integer('year', now()->year);

        $rows = Sale::query()
            ->where('payment_status', 'completed')
            ->whereYear('sold_at', $year)
            ->selectRaw('MONTH(sold_at) as month, COUNT(*) as orders, SUM(total) as revenue, SUM(tax) as tax')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => [
                'month' => (int) $r->month,
                'orders' => (int) $r->orders,
                'revenue' => round((float) $r->revenue, 2),
                'tax' => round((float) $r->tax, 2),
            ]);

        return view('reports.monthly-sales', [
            'rows' => $rows,
            'year' => $year,
            'totals' => [
                'orders' => $rows->sum('orders'),
                'revenue' => round($rows->sum('revenue'), 2),
            ],
        ]);
    }

    public function topProducts(Request $request): View
    {
        $from = Carbon::parse($request->get('from', now()->startOfMonth()))->startOfDay();
        $to = Carbon::parse($request->get('to', now()))->endOfDay();
        $limit = $request->integer('limit', 10);

        $rows = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.payment_status', 'completed')
            ->whereBetween('sales.sold_at', [$from, $to])
            ->selectRaw('products.id, products.name, products.sku, SUM(sale_items.quantity) as total_qty, SUM(sale_items.total) as revenue')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();

        return view('reports.top-products', [
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function cashierPerformance(Request $request): View
    {
        $from = Carbon::parse($request->get('from', now()->startOfMonth()))->startOfDay();
        $to = Carbon::parse($request->get('to', now()))->endOfDay();

        $rows = Sale::query()
            ->where('payment_status', 'completed')
            ->whereBetween('sold_at', [$from, $to])
            ->selectRaw('user_id, COUNT(*) as orders, SUM(total) as revenue')
            ->with('user:id,name')
            ->groupBy('user_id')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($r) => [
                'cashier' => $r->user?->name ?? 'Unknown',
                'orders' => (int) $r->orders,
                'revenue' => round((float) $r->revenue, 2),
                'avg' => $r->orders > 0 ? round((float) $r->revenue / $r->orders, 2) : 0,
            ]);

        return view('reports.cashier-performance', [
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function shiftSummary(Request $request): View
    {
        $from = Carbon::parse($request->get('from', now()->startOfMonth()))->startOfDay();
        $to = Carbon::parse($request->get('to', now()))->endOfDay();

        $shifts = Shift::query()
            ->with('user:id,name')
            ->whereBetween('opened_at', [$from, $to])
            ->orderByDesc('opened_at')
            ->paginate(15)
            ->withQueryString();

        return view('reports.shift-summary', [
            'shifts' => $shifts,
            'from' => $from,
            'to' => $to,
        ]);
    }
}