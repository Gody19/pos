<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'role_name' => ['required', 'string', 'in:admin,manager,cashier,inventory_clerk'],
            'status' => ['sometimes', 'boolean'],
        ];
    }
}
