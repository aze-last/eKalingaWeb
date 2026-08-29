<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(User::class, UserPolicy::class);

        Gate::define('manage-users', fn (User $user) => $user->canManageUsers());
        Gate::define('manage-masterlist', fn (User $user) => $user->canManageMasterlist());
        Gate::define('manage-budget', fn (User $user) => $user->canManageBudget());
        Gate::define('distribute', fn (User $user) => $user->canDistribute());
        Gate::define('view-reports', fn (User $user) => $user->canViewReports());
        Gate::define('view-ggms', fn (User $user) => $user->canViewGgms());

        if (request()->header('x-forwarded-proto') === 'https' || str_contains(request()->header('host', ''), 'lhr.life') || str_contains(request()->header('host', ''), 'ngrok')) {
            URL::forceScheme('https');
        }
    }
}
