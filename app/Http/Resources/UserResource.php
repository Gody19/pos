<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role_name' => $this->role_name,
            'roles' => $this->getRoleNames(),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'status' => $this->status,
            'active_shift' => $this->whenLoaded('shifts', fn () => $this->activeShift()?->id),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
