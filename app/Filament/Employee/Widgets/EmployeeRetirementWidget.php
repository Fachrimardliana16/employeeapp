<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeRetirementWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static bool $isLazy = true;
    
    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Pegawai Mendekati Pensiun';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()
                    ->whereNotNull('retirement')
                    ->where('retirement', '>=', now())
                    ->where('retirement', '<=', now()->addYears(5))
                    ->orderBy('retirement', 'ASC')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('retirement')
                    ->label('Tgl Pensiun')
                    ->date('d F Y'),
                Tables\Columns\TextColumn::make('remaining')
                    ->label('Sisa Waktu')
                    ->state(function (Employee $record): string {
                        $retirement = Carbon::parse($record->retirement);
                        $diff = now()->diff($retirement);
                        return "{$diff->y} thn, {$diff->m} bln";
                    }),
            ]);
    }
}
