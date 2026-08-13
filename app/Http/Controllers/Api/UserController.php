<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\AuditLogResource;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = User::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")))
            ->when($request->filled('role_name'), fn ($q) => $q->where('role_name', $request->role_name))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResource
    {
        $user = User::create($request->validated());
        $user->syncRoles([$request->role_name]);

        AuditLog::record('user_managed', $user, ['action' => 'created', 'role' => $request->role_name]);

        return new UserResource($user->load('roles'));
    }

    public function show(User $user): JsonResource
    {
        return new UserResource($user->load('roles'));
    }

    public function update(StoreUserRequest $request, User $user): JsonResource
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles([$request->role_name]);

        AuditLog::record('user_managed', $user, ['action' => 'updated', 'role' => $request->role_name]);

        return new UserResource($user->load('roles'));
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $user->status = false;
        $user->save();

        AuditLog::record('user_managed', $user, ['action' => 'deactivated']);

        return response()->json(['message' => 'User deactivated.']);
    }

    public function auditLogs(Request $request): AnonymousResourceCollection
    {
        $logs = AuditLog::query()
            ->with('user')
            ->when($request->filled('event'), fn ($q) => $q->where('event', $request->event))
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return AuditLogResource::collection($logs);
    }
}
