@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
    <x-page-header title="Edit Customer" description="Update this customer's details."
        :crumbs="['Customers' => route('customers.index'), $customer->full_name => route('customers.show', $customer)]" />

    <div class="mx-auto max-w-xl">
        <x-card title="Customer details">
            <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-input name="full_name" label="Full name" value="{{ $customer->full_name }}" autofocus />
                <x-input name="phone" label="Phone number" value="{{ $customer->phone }}" />
                <x-input name="email" label="Email address" type="email" value="{{ $customer->email }}" />
                <x-textarea name="address" label="Address (optional)" rows="3" value="{{ $customer->address }}" />

                <div class="flex items-center justify-end gap-2">
                    <x-button variant="secondary" href="{{ route('customers.show', $customer) }}">Cancel</x-button>
                    <button type="submit" class="btn-primary">Update Customer</button>
                </div>
            </form>
        </x-card>
    </div>
@endsection