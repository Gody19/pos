<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleController extends Controller
{
    public function __construct(protected SaleService $saleService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $sales = Sale::query()
            ->with(['customer', 'user', 'items.product', 'payments', 'receipt'])
            ->when($request->filled('search'), fn ($q) => $q->where('invoice_number', 'like', "%{$request->search}%"))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('shift_id'), fn ($q) => $q->where('shift_id', $request->shift_id))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->filled('from'), fn ($q) => $q->where('sold_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->where('sold_at', '<=', $request->to.' 23:59:59'))
            ->orderByDesc('sold_at')
            ->paginate($request->integer('per_page', 15));

        return SaleResource::collection($sales);
    }

    public function store(StoreSaleRequest $request): JsonResource
    {
        $sale = $this->saleService->create($request->user(), $request->validated());

        return new SaleResource($sale);
    }

    public function show(Sale $sale): JsonResource
    {
        $sale->load(['customer', 'user', 'items.product', 'payments', 'receipt', 'shift']);

        return new SaleResource($sale);
    }

    public function cancel(Request $request, Sale $sale): JsonResource
    {
        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $sale = $this->saleService->cancel($sale, $request->reason);

        return new SaleResource($sale->load(['customer', 'user', 'items.product', 'payments', 'receipt']));
    }
}
