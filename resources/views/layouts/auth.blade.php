<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('pos.store.name', 'POS') }} — @yield('title', 'Sign in')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-gray-100 px-4 py-10">
    <div class="w-full max-w-md">
        <div class="mb-6 flex flex-col items-center">
            <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-lg shadow-brand-600/30">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ config('pos.store.name', 'POS Store') }}</h1>
            <p class="mt-1 text-sm text-gray-500">Point of Sale &amp; Inventory Management System</p>
        </div>

        @include('partials.flash')

        <div class="card p-6 sm:p-8">
            @yield('auth-content')
        </div>

        <p class="mt-6 text-center text-xs text-gray-400">&copy; {{ date('Y') }} {{ config('pos.store.name', 'POS Store') }}</p>
    </div>
</body>
</html>