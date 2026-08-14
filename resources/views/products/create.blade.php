@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
    <x-page-header title="Add Product" description="Create a new product with pricing and stock."
        :crumbs="['Products' => route('products.index'), 'Add' => null]" />

    @include('products._form', [
        'routeName' => route('products.store'),
        'submitLabel' => 'Create Product',
    ])
@endsection