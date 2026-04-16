<?php

namespace App\Providers;

use App\Models\EmployeeAgreement;
use App\Models\EmployeePromotion;
use App\Observers\EmployeeAgreementObserver;
use App\Observers\EmployeePromotionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && config('telescope.enabled') !== false) {
            if (class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
                $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
                $this->app->register(TelescopeServiceProvider::class);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Auth Event Subscriber for Activity Logging
        \Illuminate\Support\Facades\Event::subscribe(\App\Listeners\LogAuthActivity::class);

        // Implicitly grant "Super Admin" role all permissions
        // This works in the local environment and when using spatie/laravel-permission
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('superadmin') ? true : null;
        });

        // Register model observers
        EmployeePromotion::observe(EmployeePromotionObserver::class);
        EmployeeAgreement::observe(EmployeeAgreementObserver::class);

        // Auto-fill users_id for all Filament forms
        \Filament\Forms\Components\Select::configureUsing(function (\Filament\Forms\Components\Select $component): void {
            if ($component->getName() === 'users_id') {
                $component
                    ->default(fn () => auth()->id())
                    ->hiddenOn(['create', 'edit'])
                    ->dehydrated();
            }
        });

        \Filament\Forms\Components\Hidden::configureUsing(function (\Filament\Forms\Components\Hidden $component): void {
            if ($component->getName() === 'users_id') {
                $component->default(fn () => auth()->id());
            }
        });
    }
}
