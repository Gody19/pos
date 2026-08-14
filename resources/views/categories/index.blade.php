@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <x-page-header title="Categories" description="Group your products into categories.">
        <x-slot:actions>
            <x-button href="{{ route('categories.create') }}">Add Category</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <x-slot:actions>
            <form method="GET">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search categories…"
                    class="input sm:w-64" autocomplete="off">
                <button type="submit" class="btn-secondary">Search</button>
            </form>
        </x-slot:actions>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="text-center">Products</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="font-semibold text-gray-900">{{ $category->name }}</td>
                            <td class="text-gray-500">{{ Str::limit($category->description, 60) }}</td>
                            <td class="text-center">
                                <x-badge color="blue">{{ $category->products_count }}</x-badge>
                            </td>
                            <td>
                                @if ($category->status)
                                    <x-badge color="green">Active</x-badge>
                                @else
                                    <x-badge color="gray">Inactive</x-badge>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('categories.edit', $category) }}" class="btn-ghost px-2 py-1">Edit</a>
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}" data-confirm="Delete this category?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost px-2 py-1 text-red-600 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state title="No categories found" description="Create categories to organise your products.">
                                    <x-slot:action>
                                        <x-button href="{{ route('categories.create') }}">Add Category</x-button>
                                    </x-slot:action>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 p-4">
            {{ $categories->links() }}
        </div>
    </x-card>
@endsection