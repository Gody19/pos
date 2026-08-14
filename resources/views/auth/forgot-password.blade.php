@extends('layouts.auth')

@section('title', 'Forgot password')

@section('auth-content')
    <h2 class="mb-1 text-lg font-bold text-gray-900">Reset your password</h2>
    <p class="mb-6 text-sm text-gray-500">Enter your email address and we will send you a password reset link.</p>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
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

        <button type="submit" class="btn-primary w-full py-2.5">
            Send reset link
        </button>

        <p class="text-center text-sm text-gray-500">
            <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">Back to sign in</a>
        </p>
    </form>
@endsection