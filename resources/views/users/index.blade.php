@extends('layouts.app')

@section('title', 'Users & Roles')

@section('content')
    <x-page-header title="Users & Roles" description="Manage system users and their permissions.">
        <x-slot:actions>
            <x-button href="{{ route('users.create') }}">Add User</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <x-slot:actions>
            <form method="GET" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email…"
                    class="input sm:w-64" autocomplete="off">
                <select name="role" class="input sm:w-44" onchange="this.form.submit()">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary">Filter</button>
            </form>
        </x-slot:actions>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Shift</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-800 text-sm font-bold text-white">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php $role = $user->roles->first()?->name; @endphp
                                <x-badge :color="match ($role) {
                                    'admin' => 'purple',
                                    'manager' => 'blue',
                                    'cashier' => 'green',
                                    default => 'gray',
                                }">{{ ucfirst($role ?? 'none') }}</x-badge>
                            </td>
                            <td>
                                @if ($user->isActive())
                                    <x-badge color="green">Active</x-badge>
                                @else
                                    <x-badge color="red">Disabled</x-badge>
                                @endif
                            </td>
                            <td>
                                @if ($user->hasOpenShift())
                                    <x-badge color="yellow">Shift open</x-badge>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('users.edit', $user) }}" class="btn-ghost px-2 py-1">Edit</a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm="Delete {{ $user->name }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost px-2 py-1 text-red-600 hover:bg-red-50">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state title="No users found" description="Add users to give them access to the system.">
                                    <x-slot:action>
                                        <x-button href="{{ route('users.create') }}">Add User</x-button>
                                    </x-slot:action>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 p-4">
            {{ $users->links() }}
        </div>
    </x-card>
@endsection