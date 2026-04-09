<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;

class EmployeeBirthdayWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Pegawai Ulang Tahun Bulan Ini';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()
                    ->whereMonth('date_birth', Carbon::now()->month)
                    ->when(config('database.default') === 'sqlite', 
                        fn ($query) => $query->orderByRaw('strftime("%d", date_birth) ASC'),
                        fn ($query) => $query->orderByRaw('DAY(date_birth) ASC')
                    )
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_birth')
                    ->label('Tgl Lahir')
                    ->date('d F'),
                Tables\Columns\TextColumn::make('age')
                    ->label('Umur')
                    ->suffix(' Tahun')
                    ->state(fn (Employee $record): int => Carbon::parse($record->date_birth)->age),
            ]);
    }
}
