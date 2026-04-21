<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SystemLogResource\Pages;
use Filament\Resources\Resource;
use Spatie\Activitylog\Models\Activity; // Dummy model as resource needs a model

class SystemLogResource extends Resource
{
    protected static ?string $model = Activity::class; // Use anything, we only use index page

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
