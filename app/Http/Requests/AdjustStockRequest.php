<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'type' => ['required', 'string', 'in:in,out,adjustment'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
