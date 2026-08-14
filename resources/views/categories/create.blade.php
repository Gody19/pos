@extends('layouts.app')

@section('title', 'Add Category')

@section('content')
    <x-page-header title="Add Category" description="Create a new product category."
        :crumbs="['Categories' => route('categories.index'), 'Add' => null]" />

    <div class="mx-auto max-w-xl">
        <x-card title="Category details">
            <form method="POST" action="{{ route('categories.store') }}" class="space-y-4">
                @csrf

                <x-input name="name" label="Category name" value="{{ old('name') }}" placeholder="e.g. Beverages" autofocus />

                <x-textarea name="description" label="Description (optional)" rows="3" value="{{ old('description') }}" placeholder="What does this category cover?" />

                <div>
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" name="status" value="1" @checked(old('status', true))
                            class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        Active
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <x-button variant="secondary" href="{{ route('categories.index') }}">Cancel</x-button>
                    <button type="submit" class="btn-primary">Create Category</button>
                </div>
            </form>
        </x-card>
    </div>
@endsection