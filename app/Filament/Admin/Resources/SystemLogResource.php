<?php

namespace App\Filament\Admin\Resources;
 
use App\Filament\Admin\Resources\SystemLogResource\Pages;
use Filament\Resources\Resource;
use App\Models\SystemLog;
 
class SystemLogResource extends Resource
{
    protected static ?string $model = SystemLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Error Log';

    protected static ?string $modelLabel = 'Error Log';

    protected static ?string $pluralModelLabel = 'Error Log';

    protected static ?string $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 10;

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemLogs::route('/'),
        ];
    }
}
