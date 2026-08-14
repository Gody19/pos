<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\ReceiptService;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(
        protected SaleService $saleService,
        protected ReceiptService $receiptService,
    ) {}

    public function index(Request $request): View
    {
        $sales = Sale::query()
            ->with(['customer', 'user', 'shift', 'items.product'])
            ->when($request->filled('search'), fn ($q) => $q->where('invoice_number', 'like', '%'.$request->search.'%'))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->filled('payment_method'), fn ($q) => $q->where('payment_method', $request->payment_method))
            ->when($request->filled('from'), fn ($q) => $q->where('sold_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->where('sold_at', '<=', $request->to.' 23:59:59'))
            ->orderByDesc('sold_at')
            ->paginate(20)
            ->withQueryString();

        return view('sales.index', ['sales' => $sales]);
    }

    public function show(Sale $sale): View
    {
        $sale->load(['customer', 'user', 'items.product', 'payments', 'receipt', 'shift', 'loyaltyTransactions']);

        return view('sales.show', ['sale' => $sale]);
    }

    public function cancel(Request $request, Sale $sale): RedirectResponse
    {
        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $this->saleService->cancel($sale, $request->reason);

        return redirect()->route('sales.show', $sale)->with('success', 'Sale cancelled and stock restored.');
    }

    public function receipt(Sale $sale): View
    {
        $sale->load(['items.product', 'customer', 'user', 'payments']);

        $receipt = $sale->receipt ?? $this->receiptService->generate($sale);

        return view('sales.receipt', [
            'sale' => $sale,
            'receipt' => $receipt,
            'qrDataUri' => $this->receiptService->qrDataUri($sale->invoice_number),
        ]);
    }

    public function printReceipt(Sale $sale): View
    {
        return view('receipts.receipt', [
            'sale' => $sale->load(['items.product', 'customer', 'user', 'payments']),
            'receipt' => $sale->receipt,
            'qrDataUri' => $this->receiptService->qrDataUri($sale->invoice_number),
        ]);
    }

    public function downloadReceipt(Sale $sale): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $this->receiptService->download($sale);
    }
}