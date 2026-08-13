<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\ReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ReceiptController extends Controller
{
    public function __construct(protected ReceiptService $receiptService) {}

    public function download(Sale $sale): Response
    {
        return $this->receiptService->download($sale);
    }

    public function html(Sale $sale): JsonResponse
    {
        return response()->json(['data' => $this->receiptService->html($sale)]);
    }
}
