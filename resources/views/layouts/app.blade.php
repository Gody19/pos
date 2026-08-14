<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('pos.store.name', 'POS') }} — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full">
    <div class="flex h-full">
        {{-- Sidebar overlay (mobile) --}}
        <div data-sidebar-overlay class="fixed inset-0 z-30 hidden bg-gray-900/50 backdrop-blur-sm"></div>

        {{-- Sidebar --}}
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-gray-900 transition-transform duration-200 lg:static lg:translate-x-0">
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-800 px-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <span class="text-lg font-bold text-white">{{ config('pos.store.name', 'POS Store') }}</span>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 scrollbar-thin">
                @php
                    $user = auth()->user();
                    $current = request()->route()?->getName() ?? '';
                @endphp

                @if ($user->can('view dashboard'))
                    <x-nav-link :route="'dashboard'" :current="$current" label="Dashboard">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10" />
                        </svg>
                    </x-nav-link>
                @endif

                @if ($user->can('create sales'))
                    <x-nav-link :route="'pos.index'" :current="$current" label="POS Terminal" :badge="$user->activeShift() ? null : 'No shift'">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a1 1 0 011 1v10a1 1 0 01-1 1H5a1 1 0 01-1-1V7a1 1 0 011-1zm6 9h6m-6-3h2" />
                        </svg>
                    </x-nav-link>
                @endif

                @if ($user->can('view sales'))
                    <x-nav-link :route="'sales.index'" :current="$current" label="Sales">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3-2 2 2 2-2 2 2 2-2 3 2z" />
                        </svg>
                    </x-nav-link>
                @endif

                @if ($user->can('manage shifts'))
                    <div class="pt-3">
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Shifts</p>
                        <div class="space-y-1">
                            <x-nav-link :route="'shifts.open'" :current="$current" label="Open Shift">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </x-nav-link>
                            <x-nav-link :route="'shifts.close'" :current="$current" label="Close Shift">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12M16 6l2 2-6 6-2-2 6-6z" />
                                </svg>
                            </x-nav-link>
                            <x-nav-link :route="'shifts.history'" :current="$current" label="Shift History">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </x-nav-link>
                        </div>
                    </div>
                @endif

                @if ($user->can('view products'))
                    <div class="pt-3">
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Catalog</p>
                        <div class="space-y-1">
                            <x-nav-link :route="'products.index'" :current="$current" label="Products">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </x-nav-link>
                            @if ($user->can('manage categories'))
                                <x-nav-link :route="'categories.index'" :current="$current" label="Categories">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                                    </svg>
                                </x-nav-link>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($user->can('manage inventory'))
                    <div class="pt-3">
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Inventory</p>
                        <div class="space-y-1">
                            <x-nav-link :route="'inventory.index'" :current="$current" label="Stock Levels">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                            </x-nav-link>
                            <x-nav-link :route="'inventory.movements'" :current="$current" label="Movements">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v6a4 4 0 004 4h9M3 7V4h11a4 4 0 014 4v9M3 7l3 3m15-3v6a4 4 0 01-4 4h-9" />
                                </svg>
                            </x-nav-link>
                        </div>
                    </div>
                @endif

                @if ($user->can('view customers'))
                    <div class="pt-3">
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Customers</p>
                        <x-nav-link :route="'customers.index'" :current="$current" label="Customers">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </x-nav-link>
                    </div>
                @endif

                @if ($user->can('view reports'))
                    <div class="pt-3">
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Reports</p>
                        <div class="space-y-1">
                            <x-nav-link :route="'reports.daily'" :current="$current" label="Daily Sales">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </x-nav-link>
                            <x-nav-link :route="'reports.monthly'" :current="$current" label="Monthly Sales">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </x-nav-link>
                            <x-nav-link :route="'reports.top-products'" :current="$current" label="Top Products">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </x-nav-link>
                            <x-nav-link :route="'reports.cashier-performance'" :current="$current" label="Cashier Performance">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
                                </svg>
                            </x-nav-link>
                            <x-nav-link :route="'reports.shift-summary'" :current="$current" label="Shift Summary">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </x-nav-link>
                        </div>
                    </div>
                @endif

                @if ($user->hasRole('admin'))
                    <div class="pt-3">
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Administration</p>
                        <div class="space-y-1">
                            <x-nav-link :route="'users.index'" :current="$current" label="Users & Roles">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </x-nav-link>
                            <x-nav-link :route="'audit-logs.index'" :current="$current" label="Audit Logs">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </x-nav-link>
                        </div>
                    </div>
                @endif
            </nav>

            <div class="border-t border-gray-800 p-4">
                <p class="text-xs text-gray-500">Powered by <span class="text-gray-400 font-medium">POS System</span></p>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Top bar --}}
            <header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 bg-white px-4 sm:px-6">
                <button type="button" data-toggle-sidebar class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 lg:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-gray-800">
                        {{ $pageTitle ?? (trim($__env->yieldContent('title')) ?: 'Dashboard') }}
                    </p>
                    <p class="hidden text-xs text-gray-400 sm:block">{{ now()->format('l, d F Y · H:i') }}</p>
                </div>

                <div class="flex items-center gap-3">
                    @if (auth()->user()->can('create sales'))
                        <a href="{{ route('pos.index') }}"
                            class="hidden items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 sm:inline-flex">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            New Sale
                        </a>
                    @endif

                    @if ($activeShift = auth()->user()->activeShift())
                        <span class="hidden items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 md:inline-flex">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Shift open · {{ number_format($activeShift->opening_balance, 0) }}
                        </span>
                    @endif

                    <div class="relative">
                        <button type="button" data-user-menu
                            class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-gray-100">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-800 text-sm font-bold text-white">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden text-left md:block">
                                <p class="text-sm font-semibold leading-tight text-gray-800">{{ auth()->user()->name }}</p>
                                <p class="text-xs capitalize leading-tight text-gray-400">{{ auth()->user()->role_name ?? 'User' }}</p>
                            </div>
                            <svg class="hidden h-4 w-4 text-gray-400 md:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div data-user-dropdown
                            class="absolute right-0 mt-2 hidden w-52 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg">
                            <div class="border-b border-gray-100 px-4 py-3">
                                <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-gray-400">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="p-1.5">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-4 sm:p-6">
                @include('partials.flash')
                @yield('content')
            </main>

            <footer class="border-t border-gray-200 px-6 py-4 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} {{ config('pos.store.name', 'POS Store') }} · POS &amp; Inventory Management System
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>