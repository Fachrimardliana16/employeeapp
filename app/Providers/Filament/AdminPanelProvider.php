<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Rmsramos\Activitylog\ActivitylogPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Admin Panel')
            ->favicon(asset('favicon.png'))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->plugins([
                ActivitylogPlugin::make(),
            ])
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->widgets([
                // Default widgets removed
            ])
            ->navigationGroups([
                'Teknikal documentation',
                'pengaturan sistem',
                'Pengaturan payroll',
                'Manajemen Pengguna',
                'Sistem',
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Panel Kepagawaian')
                    ->url('/employee')
                    ->icon('heroicon-o-building-office-2')
                    ->visible(fn() => auth()->user()?->hasAnyRole(['superadmin', 'admin'])),
                MenuItem::make()
                    ->label('Panel Pegawai')
                    ->url('/user')
                    ->icon('heroicon-o-users')
                    ->visible(fn() => auth()->user()?->hasAnyRole(['superadmin', 'admin'])),
            ])
            ->authMiddleware([
                Authenticate::class,
                'check.role:superadmin',
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ]);
    }
}
