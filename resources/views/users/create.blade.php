@extends('layouts.app')

@section('title', 'Add User')

@section('content')
    <x-page-header title="Add User" description="Create a new system user account."
        :crumbs="['Users' => route('users.index'), 'Add' => null]" />

    <div class="mx-auto max-w-xl">
        <x-card title="Account details">
            <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
                @csrf

                <x-input name="name" label="Full name" value="{{ old('name') }}" placeholder="e.g. Jane Cashier" autofocus />
                <x-input name="email" label="Email address" type="email" value="{{ old('email') }}" placeholder="user@example.com" />
                <x-input name="phone" label="Phone number" value="{{ old('phone') }}" placeholder="+255 700 000 000" />
                <x-select name="role_name" label="Role" :options="[
                    'admin' => 'Admin',
                    'manager' => 'Manager',
                    'cashier' => 'Cashier',
                    'inventory_clerk' => 'Inventory Clerk',
                ]" selected="{{ old('role_name', 'cashier') }}" placeholder="Select a role" />
                <x-input name="password" label="Password" type="password" placeholder="At least 8 characters" />
                <x-input name="password_confirmation" label="Confirm password" type="password" placeholder="Repeat password" />

                <div>
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" name="status" value="1" @checked(old('status', true))
                            class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        Active (can sign in)
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <x-button variant="secondary" href="{{ route('users.index') }}">Cancel</x-button>
                    <button type="submit" class="btn-primary">Create User</button>
                </div>
            </form>
        </x-card>
    </div>
@endsection