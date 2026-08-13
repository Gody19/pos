<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_code' => $this->customer_code,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'loyalty_points' => $this->loyalty_points,
            'total_spent' => $this->whenAggregated('sales', 'total', 'sum'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
