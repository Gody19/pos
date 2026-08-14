@extends('layouts.app')

@section('title', 'Add Customer')

@section('content')
    <x-page-header title="Add Customer" description="Create a new customer profile."
        :crumbs="['Customers' => route('customers.index'), 'Add' => null]" />

    <div class="mx-auto max-w-xl">
        <x-card title="Customer details">
            <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
                @csrf

                <x-input name="full_name" label="Full name" value="{{ old('full_name') }}" placeholder="e.g. John Doe" autofocus />
                <x-input name="phone" label="Phone number" value="{{ old('phone') }}" placeholder="e.g. +255 712 345 678" />
                <x-input name="email" label="Email address" type="email" value="{{ old('email') }}" placeholder="customer@example.com" />
                <x-textarea name="address" label="Address (optional)" rows="3" value="{{ old('address') }}" placeholder="Street, city, region…" />

                <div class="flex items-center justify-end gap-2">
                    <x-button variant="secondary" href="{{ route('customers.index') }}">Cancel</x-button>
                    <button type="submit" class="btn-primary">Create Customer</button>
                </div>
            </form>
        </x-card>
    </div>
@endsection