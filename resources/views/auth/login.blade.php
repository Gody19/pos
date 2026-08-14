@extends('layouts.auth')

@section('title', 'Sign in')

@section('auth-content')
    <h2 class="mb-1 text-lg font-bold text-gray-900">Sign in to your account</h2>
    <p class="mb-6 text-sm text-gray-500">Enter your credentials to access the terminal.</p>

    <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
        @csrf

        <x-input
            name="email"
            label="Email address"
            type="email"
            placeholder="you@example.com"
            value="{{ old('email') }}"
            autocomplete="email"
            autofocus
        />

        <x-input
            name="password"
            label="Password"
            type="password"
            placeholder="••••••••"
            autocomplete="current-password"
        />

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" value="1"
                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">
                Forgot password?
            </a>
        </div>

        <button type="submit" class="btn-primary w-full py-2.5">
            Sign in
        </button>
    </form>
@endsection