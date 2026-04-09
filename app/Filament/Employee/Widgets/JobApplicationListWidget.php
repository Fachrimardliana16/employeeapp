<?php

namespace App\Filament\Employee\Widgets;

use App\Models\JobApplication;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class JobApplicationListWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Lamaran Pekerjaan Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                JobApplication::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('application_number')
                    ->label('No. Lamaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('appliedPosition.name')
                    ->label('Jabatan'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'reviewed' => 'warning',
                        'interview_scheduled' => 'primary',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ]);
    }
}
