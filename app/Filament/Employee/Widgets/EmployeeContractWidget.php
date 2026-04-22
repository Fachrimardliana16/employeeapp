<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;

class EmployeeContractWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected static bool $isLazy = true;
    
    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Pegawai Habis Kontrak';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()
                    ->whereNotNull('agreement_date_end')
                    ->where('agreement_date_end', '>=', now())
                    ->where('agreement_date_end', '<=', now()->addMonths(6))
                    ->orderBy('agreement_date_end', 'ASC')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('agreement.name')
                    ->label('Tipe Kontrak'),
                Tables\Columns\TextColumn::make('agreement_date_end')
                    ->label('Tgl Berakhir')
                    ->date('d F Y')
                    ->color('danger'),
            ]);
    }
}
