<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $today = Carbon::today();

        $todaySales = Sale::where('payment_status', 'completed')->whereDate('sold_at', $today);

        $todayRevenue = (float) (clone $todaySales)->sum('total');
        $todayOrders = (clone $todaySales)->count();

        $todayProfit = Sale::where('payment_status', 'completed')
            ->whereDate('sold_at', $today)
            ->with('items.product:id,cost_price')
            ->get()
            ->sum(fn (Sale $sale) => $sale->profit());

        $openShifts = Shift::where('status', 'open')->count();

        $lowStock = Inventory::whereColumn('quantity', '<=', 'reorder_level')->count();

        $totalRevenue = Sale::where('payment_status', 'completed')->sum('total');

        $paymentMix = Sale::where('payment_status', 'completed')
            ->whereDate('sold_at', $today)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as amount')
            ->groupBy('payment_method')
            ->get();

        $hourly = Sale::where('payment_status', 'completed')
            ->whereDate('sold_at', $today)
            ->selectRaw('HOUR(sold_at) as hour, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $trend = Sale::where('payment_status', 'completed')
            ->whereBetween('sold_at', [now()->subDays(13)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(sold_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.payment_status', 'completed')
            ->whereBetween('sales.sold_at', [now()->subDays(13)->startOfDay(), now()->endOfDay()])
            ->selectRaw('products.name, SUM(sale_items.quantity) as total_qty, SUM(sale_items.total) as revenue')
            ->groupBy('products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'today_revenue' => $todayRevenue,
                'today_orders' => $todayOrders,
                'today_profit' => round($todayProfit, 2),
                'open_shifts' => $openShifts,
                'low_stock_items' => $lowStock,
                'total_revenue' => round($totalRevenue, 2),
                'payment_mix' => $paymentMix,
                'hourly_sales' => $hourly,
                'sales_trend' => $trend,
                'top_products' => $topProducts,
            ],
        ]);
    }
}
