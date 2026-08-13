<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'customer_id' => $this->customer_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'user_id' => $this->user_id,
            'shift_id' => $this->shift_id,
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'receipt' => new ReceiptResource($this->whenLoaded('receipt')),
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'tax' => (float) $this->tax,
            'total' => (float) $this->total,
            'amount_paid' => (float) $this->amount_paid,
            'change_due' => (float) $this->change_due,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'sold_at' => $this->sold_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
