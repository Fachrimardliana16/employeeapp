<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;

class ManageCache extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static string $view = 'filament.admin.pages.manage-cache';

    protected static ?string $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Kelola Cache';

    protected static ?string $title = 'Kelola Cache';

    protected static ?int $navigationSort = 10;

    public function clearAllCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            Notification::make()
                ->title('Semua cache berhasil dibersihkan!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal membersihkan cache')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function clearAppCache()
    {
        Artisan::call('cache:clear');
        Notification::make()->title('Application Cache dibersihkan')->success()->send();
    }

    public function clearConfigCache()
    {
        Artisan::call('config:clear');
        Notification::make()->title('Configuration Cache dibersihkan')->success()->send();
    }

    public function clearRouteCache()
    {
        Artisan::call('route:clear');
        Notification::make()->title('Route Cache dibersihkan')->success()->send();
    }

    public function clearViewCache()
    {
        Artisan::call('view:clear');
        Notification::make()->title('Compiled Views dibersihkan')->success()->send();
    }

    public function optimizeApp()
    {
        Artisan::call('optimize');
        Notification::make()->title('Aplikasi Berhasil Dioptimasi (Config & Route Cached)')->success()->send();
    }
}
