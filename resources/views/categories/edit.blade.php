@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
    <x-page-header title="Edit Category" description="Update this category's details."
        :crumbs="['Categories' => route('categories.index'), $category->name => route('categories.edit', $category)]" />

    <div class="mx-auto max-w-xl">
        <x-card title="Category details">
            <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-input name="name" label="Category name" value="{{ $category->name }}" autofocus />

                <x-textarea name="description" label="Description (optional)" rows="3" value="{{ $category->description }}" />

                <div>
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" name="status" value="1" @checked($category->status)
                            class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        Active
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <x-button variant="secondary" href="{{ route('categories.index') }}">Cancel</x-button>
                    <button type="submit" class="btn-primary">Update Category</button>
                </div>
            </form>
        </x-card>
    </div>
@endsection