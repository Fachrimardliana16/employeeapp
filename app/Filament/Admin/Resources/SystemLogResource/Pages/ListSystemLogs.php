<?php

namespace App\Filament\Admin\Resources\SystemLogResource\Pages;

use App\Filament\Admin\Resources\SystemLogResource;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ListSystemLogs extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = SystemLogResource::class;

    protected static string $view = 'filament.admin.resources.system-log-resource.pages.list-system-logs';

    public function getTitle(): string
    {
        return 'Error Log';
    }

    /**
     * Use standard Filament table definition.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query()->whereRaw('1=0')) // Dummy query to satisfy the builder
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->fontFamily('mono')
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'error', 'critical', 'alert', 'emergency' => 'danger',
                        'warning' => 'warning',
                        'notice' => 'info',
                        'info' => 'primary',
                        'debug' => 'gray',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('message')
                    ->label('Pesan')
                    ->wrap()
                    ->searchable(),
            ])
            ->defaultSort('date', 'desc');
    }

    /**
     * Provide log data from the file as table records.
     * We return an Eloquent Collection of Activity models hydrated with log data
     * to satisfy Filament's standard Table Builder requirements.
     */
    public function getTableRecords(): EloquentCollection
    {
        $logPath = storage_path('logs/laravel.log');

        if (!File::exists($logPath)) {
            return new EloquentCollection();
        }

        // Use shell tail command for memory efficiency
        $lastLines = shell_exec("tail -n 500 " . escapeshellarg($logPath));
        
        if (empty($lastLines)) {
            return new EloquentCollection();
        }

        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*)/m';
        preg_match_all($pattern, $lastLines, $matches, PREG_SET_ORDER);

        $logs = new EloquentCollection();
        foreach (array_reverse($matches) as $index => $match) {
            // We hydrate the Activity model with our log data
            $activity = new Activity();
            $activity->id = $index + 1;
            $activity->setAttribute('date', $match[1]);
            $activity->setAttribute('level', $match[3]);
            $activity->setAttribute('message', $match[4]);
            
            $logs->add($activity);
        }

        return $logs;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Download Log')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $logPath = storage_path('logs/laravel.log');
                    if (File::exists($logPath)) {
                        return Response::download($logPath);
                    }
                    
                    Notification::make()
                        ->title('Berkas log tidak ditemukan.')
                        ->warning()
                        ->send();
                }),

            Action::make('clear')
                ->label('Bersihkan Log')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    $logPath = storage_path('logs/laravel.log');
                    if (File::exists($logPath)) {
                        File::put($logPath, '');
                        
                        Notification::make()
                            ->title('Log berhasil dibersihkan.')
                            ->success()
                            ->send();
                    }
                }),
        ];
    }
}
