@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
    <x-page-header title="Edit Product" description="Update product information, pricing and stock."
        :crumbs="['Products' => route('products.index'), $product->name => route('products.edit', $product)]" />

    @include('products._form', [
        'product' => $product,
        'routeName' => route('products.update', $product),
        'submitLabel' => 'Update Product',
    ])
@endsection