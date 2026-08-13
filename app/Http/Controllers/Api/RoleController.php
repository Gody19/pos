<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Cache::remember('pos_roles_permissions', 3600, fn () => Role::with('permissions')->get());

        return response()->json([
            'data' => $roles->map(fn (Role $role) => [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ]),
        ]);
    }

    public function permissions(): JsonResponse
    {
        return response()->json(['data' => Permission::orderBy('name')->pluck('name')]);
    }

    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::findByName($request->role);
        $role->syncPermissions($request->permissions);

        return response()->json([
            'message' => 'Role permissions updated.',
            'data' => $role->load('permissions'),
        ]);
    }
}
