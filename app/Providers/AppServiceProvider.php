<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') || env('APP_ENV') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('doctor', fn (User $user) => $user->isDoctor());
        Gate::define('secretary', fn (User $user) => $user->isSecretary());

        // Redirect if doctor subscription is inactive
        Route::aliasMiddleware('subscription.active', \App\Http\Middleware\CheckSubscription::class);
    }
}
