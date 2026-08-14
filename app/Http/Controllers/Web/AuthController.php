<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (RateLimiter::tooManyAttempts('login:'.$request->ip(), 5)) {
            $seconds = RateLimiter::availableIn('login:'.$request->ip());

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if ($user === null || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit('login:'.$request->ip());

            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        if (! $user->isActive()) {
            RateLimiter::hit('login:'.$request->ip());

            throw ValidationException::withMessages([
                'email' => 'This account has been deactivated. Contact your administrator.',
            ]);
        }

        RateLimiter::clear('login:'.$request->ip());

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        AuditLog::record('login', $user, ['ip' => $request->ip()]);

        $intended = $request->session()->pull('url.intended');

        return redirect()->to($intended ?? route('dashboard'))->with('success', 'Welcome back, '.$user->name.'!');
    }

    public function logout(Request $request): RedirectResponse
    {
        AuditLog::record('logout', $request->user());

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}