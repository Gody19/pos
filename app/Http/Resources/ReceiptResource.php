<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'receipt_number' => $this->receipt_number,
            'sale_id' => $this->sale_id,
            'pdf_url' => $this->pdf_path ? url('api/receipts/'.$this->sale_id.'/download') : null,
            'emailed' => $this->emailed,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
