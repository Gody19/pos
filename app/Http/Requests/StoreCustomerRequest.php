<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')?->id;

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32', Rule::unique('customers', 'phone')->ignore($customerId)->whereNull('deleted_at')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customerId)->whereNull('deleted_at')],
            'address' => ['nullable', 'string'],
            'loyalty_points' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
