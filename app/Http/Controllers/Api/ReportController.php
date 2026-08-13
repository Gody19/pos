<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShiftResource;
use App\Models\Sale;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function dailySales(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->get('from', now()->startOfMonth()));
        $to = Carbon::parse($request->get('to', now()->endOfDay()));

        $rows = Sale::query()
            ->where('payment_status', 'completed')
            ->whereBetween('sold_at', [$from, $to])
            ->selectRaw('DATE(sold_at) as date, COUNT(*) as orders, SUM(total) as revenue, SUM(subtotal - discount) as gross')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($r) => [
                'date' => $r->date,
                'orders' => (int) $r->orders,
                'revenue' => round((float) $r->revenue, 2),
                'gross' => round((float) $r->gross, 2),
            ]);

        return response()->json(['data' => $rows]);
    }

    public function monthlySales(Request $request): JsonResponse
    {
        $year = $request->integer('year', now()->year);

        $rows = Sale::query()
            ->where('payment_status', 'completed')
            ->whereYear('sold_at', $year)
            ->selectRaw('MONTH(sold_at) as month, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => [
                'month' => (int) $r->month,
                'orders' => (int) $r->orders,
                'revenue' => round((float) $r->revenue, 2),
            ]);

        return response()->json(['data' => $rows, 'year' => $year]);
    }

    public function topProducts(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->get('from', now()->startOfMonth()));
        $to = Carbon::parse($request->get('to', now()->endOfDay()));
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

        return response()->json(['data' => $rows]);
    }

    public function cashierPerformance(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->get('from', now()->startOfMonth()));
        $to = Carbon::parse($request->get('to', now()->endOfDay()));

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

        return response()->json(['data' => $rows]);
    }

    public function shiftSummary(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->get('from', now()->startOfMonth()));
        $to = Carbon::parse($request->get('to', now()->endOfDay()));

        $shifts = Shift::query()
            ->with('user:id,name')
            ->whereBetween('opened_at', [$from, $to])
            ->orderByDesc('opened_at')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => ShiftResource::collection($shifts),
        ]);
    }
}
