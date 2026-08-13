<?php

namespace App\Services;

use App\Models\Receipt;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptService
{
    public function generate(Sale $sale): Receipt
    {
        $sale->load(['items.product', 'customer', 'user', 'payments']);

        $receipt = Receipt::firstOrCreate(
            ['sale_id' => $sale->id],
            ['receipt_number' => 'RCP-'.strtoupper(Str::random(10))],
        );

        $pdf = Pdf::loadView('receipts.receipt', [
            'sale' => $sale,
            'receipt' => $receipt,
            'qrDataUri' => $this->qrDataUri($sale->invoice_number),
        ])->setPaper([0, 0, 300, 0], 'portrait'); // 80mm thermal roll

        $filename = 'receipts/'.$receipt->receipt_number.'.pdf';
        Storage::disk('local')->put($filename, $pdf->output());

        $receipt->update(['pdf_path' => $filename]);

        return $receipt;
    }

    public function download(Sale $sale): StreamedResponse
    {
        $sale->load(['items.product', 'customer', 'user', 'payments']);

        $receipt = $sale->receipt;

        $filename = $receipt?->pdf_path ?? ('receipts/RCP-'.Str::random(10).'.pdf');

        if ($receipt === null || ! Storage::disk('local')->exists($filename)) {
            $receipt = $this->generate($sale);
            $filename = $receipt->pdf_path;
        }

        return Storage::disk('local')->download($filename);
    }

    public function html(Sale $sale): string
    {
        $sale->load(['items.product', 'customer', 'user', 'payments']);

        return view('receipts.receipt', [
            'sale' => $sale,
            'receipt' => $sale->receipt,
            'qrDataUri' => $this->qrDataUri($sale->invoice_number),
        ])->render();
    }

    protected function qrDataUri(string $payload): string
    {
        $result = (new Builder(
            writer: new PngWriter,
            writerOptions: [],
            validateResult: false,
            data: $payload,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 96,
            margin: 0,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        ))->build();

        return 'data:image/png;base64,'.base64_encode($result->getString());
    }
}
