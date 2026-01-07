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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
