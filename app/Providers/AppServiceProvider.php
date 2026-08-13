<?php

namespace App\Providers;

use App\Events\LowStockNotification;
use App\Listeners\LowStockListener;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            return $user->role_name === 'admin' ? true : null;
        });

        RateLimiter::for('api', fn ($request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('login', fn ($request) => Limit::perMinute(5)->by($request->ip()));

        Event::listen(LowStockNotification::class, LowStockListener::class);
    }
}
