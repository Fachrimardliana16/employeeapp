<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Carbon;

class ManageBackup extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static string $view = 'filament.admin.pages.manage-backup';

    protected static ?string $navigationGroup = 'pengaturan sistem';

    protected static ?string $navigationLabel = 'Kelola Backup';

    protected static ?string $title = 'Kelola Backup';

    protected static ?string $slug = 'manage-backup';

    protected static ?int $navigationSort = 11;

    public function getBackups(): Collection
    {
        $backupDisk = Storage::disk('local');
        $backupFolder = 'employeeapp'; 

        if (!$backupDisk->exists($backupFolder)) {
            return collect();
        }

        $files = $backupDisk->files($backupFolder);

        return collect($files)->map(function ($file) use ($backupDisk) {
            return [
                'name' => basename($file),
                'size' => round($backupDisk->size($file) / 1024 / 1024, 2) . ' MB',
                'created_at' => Carbon::createFromTimestamp($backupDisk->lastModified($file))->format('d/m/Y H:i:s'),
                'path' => $file,
            ];
        })->sortByDesc('created_at');
    }

    public function downloadBackup($path)
    {
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path);
        }
        Notification::make()->title('Berkas tidak ditemukan')->danger()->send();
    }

    public function deleteBackup($path)
    {
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
            Notification::make()->title('Backup berhasil dihapus')->success()->send();
        }
    }

    public function runFullBackup()
    {
        Notification::make()->title('Proses Backup Dimulai (Seluruh Aplikasi)')->info()->send();
        Artisan::call('backup:run');
        Notification::make()->title('Backup Berhasil!')->success()->send();
    }

    public function runDbBackup()
    {
        Notification::make()->title('Proses Backup Database Dimulai')->info()->send();
        Artisan::call('backup:run', ['--only-db' => true]);
        Notification::make()->title('Backup Database Berhasil!')->success()->send();
    }
}
