@extends('layouts.auth')

@section('title', 'Reset password')

@section('auth-content')
    <h2 class="mb-1 text-lg font-bold text-gray-900">Choose a new password</h2>
    <p class="mb-6 text-sm text-gray-500">Your password must be at least 8 characters long.</p>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-input
            name="email"
            label="Email address"
            type="email"
            placeholder="you@example.com"
            value="{{ old('email', $email) }}"
            autocomplete="email"
        />

        <x-input
            name="password"
            label="New password"
            type="password"
            placeholder="••••••••"
            autocomplete="new-password"
        />

        <x-input
            name="password_confirmation"
            label="Confirm new password"
            type="password"
            placeholder="••••••••"
            autocomplete="new-password"
        />

        <button type="submit" class="btn-primary w-full py-2.5">
            Reset password
        </button>

        <p class="text-center text-sm text-gray-500">
            <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">Back to sign in</a>
        </p>
    </form>
@endsection