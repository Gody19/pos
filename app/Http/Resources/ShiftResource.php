<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'opening_balance' => (float) $this->opening_balance,
            'closing_balance' => $this->closing_balance !== null ? (float) $this->closing_balance : null,
            'expected_balance' => $this->expected_balance !== null ? (float) $this->expected_balance : null,
            'cash_sales' => (float) $this->cash_sales,
            'card_sales' => (float) $this->card_sales,
            'mobile_sales' => (float) $this->mobile_sales,
            'qr_sales' => (float) $this->qr_sales,
            'total_sales' => (float) $this->totalSales(),
            'sales_count' => $this->sales_count,
            'opened_at' => $this->opened_at?->toISOString(),
            'closed_at' => $this->closed_at?->toISOString(),
            'status' => $this->status,
            'notes' => $this->notes,
            'variance' => $this->closing_balance !== null && $this->expected_balance !== null
                ? round((float) $this->closing_balance - (float) $this->expected_balance, 2)
                : null,
        ];
    }
}
