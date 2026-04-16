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
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class EmployeePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('employee')
            ->path('employee')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->brandName('Manajemen Pegawai')

            ->favicon(asset('images/favicon.ico'))
            ->discoverResources(in: app_path('Filament/Employee/Resources'), for: 'App\\Filament\\Employee\\Resources')
            ->discoverPages(in: app_path('Filament/Employee/Pages'), for: 'App\\Filament\\Employee\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])

            ->discoverWidgets(in: app_path('Filament/Employee/Widgets'), for: 'App\\Filament\\Employee\\Widgets')
            ->widgets([
                \App\Filament\Employee\Widgets\EmployeeStats::class,
                \App\Filament\Employee\Widgets\JobApplicationListWidget::class,
                \App\Filament\Employee\Widgets\EmployeeBirthdayWidget::class,
                \App\Filament\Employee\Widgets\EmployeeRetirementWidget::class,
                \App\Filament\Employee\Widgets\EmployeeRetiredListWidget::class,
                \App\Filament\Employee\Widgets\EmployeeContractWidget::class,
                \App\Filament\Employee\Widgets\EmployeeStatusChart::class,
                \App\Filament\Employee\Widgets\EmployeeEducationChart::class,
                \App\Filament\Employee\Widgets\EmployeeGenderChart::class,
                \App\Filament\Employee\Widgets\TodayAttendanceChart::class,
                \App\Filament\Employee\Widgets\EmployeeGrowthChart::class,
                \App\Filament\Employee\Widgets\DailyAttendanceTrendChart::class,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Panel Admin')
                    ->url('/admin')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->visible(fn () => auth()->user()?->hasRole('superadmin')),
                MenuItem::make()
                    ->label('Panel Pegawai')
                    ->url('/user')
                    ->icon('heroicon-o-users')
                    ->visible(fn () => auth()->user()?->hasRole('superadmin')),
            ])
            ->authMiddleware([
                Authenticate::class,
                'check.role:admin',
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
            ])
            ->navigationGroups([
                'Rekrutmen & Seleksi',
                'Manajemen Pegawai',
                'Kompensasi & Tunjangan',
                'Operasional Pegawai',
                'Absensi & Kehadiran',
                'Kinerja & Pengembangan',
                'Surat & Tugas Dinas',
                'Master Data',
                'Bantuan',
            ])
            ->sidebarCollapsibleOnDesktop()
            ->spa();
    }
}
