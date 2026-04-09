<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\ActivityLogResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Spatie\Activitylog\Models\Activity;

class ActivityLogTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Activities';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->placeholder('System')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Activity')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Target')
                    ->formatStateUsing(fn ($state) => str(class_basename($state))->headline())
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime()
                    ->description(fn (Activity $record): string => $record->created_at->diffForHumans())
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Activity $record): string => ActivityLogResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye')
                    ->color('info'),
            ]);
    }
}
