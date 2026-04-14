<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;

class EmployeeRetiredListWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Data Pensiunan (5 Tahun Terakhir)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()
                    ->whereNotNull('retirement')
                    ->where('retirement', '<=', now())
                    ->where('retirement', '>=', now()->subYears(5))
                    ->orderBy('retirement', 'DESC')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nippam')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position.name')
                    ->label('Jabatan Terakhir')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Unit Kerja')
                    ->searchable(),
                Tables\Columns\TextColumn::make('retirement')
                    ->label('Tanggal Pensiun')
                    ->date('d F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('age_at_retirement')
                    ->label('Usia Pensiun')
                    ->state(function (Employee $record): string {
                        if (!$record->date_birth || !$record->retirement) return '-';
                        $birth = Carbon::parse($record->date_birth);
                        $retirement = Carbon::parse($record->retirement);
                        return $birth->diffInYears($retirement) . ' Tahun';
                    }),
            ]);
    }
}
