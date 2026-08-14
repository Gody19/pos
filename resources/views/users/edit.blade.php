@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <x-page-header title="Edit User" description="Update this user's account and role."
        :crumbs="['Users' => route('users.index'), $user->name => route('users.edit', $user)]" />

    <div class="mx-auto max-w-xl">
        <x-card title="Account details">
            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-input name="name" label="Full name" value="{{ $user->name }}" autofocus />
                <x-input name="email" label="Email address" type="email" value="{{ $user->email }}" />
                <x-input name="phone" label="Phone number" value="{{ $user->phone }}" />
                <x-select name="role_name" label="Role" :options="[
                    'admin' => 'Admin',
                    'manager' => 'Manager',
                    'cashier' => 'Cashier',
                    'inventory_clerk' => 'Inventory Clerk',
                ]" selected="{{ $user->role_name }}" />
                <x-input name="password" label="New password (leave blank to keep)" type="password" placeholder="At least 8 characters" />
                <x-input name="password_confirmation" label="Confirm new password" type="password" placeholder="Repeat password" />

                <div>
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" name="status" value="1" @checked($user->isActive())
                            class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        Active (can sign in)
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <x-button variant="secondary" href="{{ route('users.index') }}">Cancel</x-button>
                    <button type="submit" class="btn-primary">Update User</button>
                </div>
            </form>
        </x-card>
    </div>
@endsection