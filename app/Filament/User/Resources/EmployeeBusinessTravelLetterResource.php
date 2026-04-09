<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\EmployeeBusinessTravelLetterResource\Pages;
use App\Models\EmployeeBusinessTravelLetter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EmployeeBusinessTravelLetterResource extends Resource
{
    protected static ?string $model = EmployeeBusinessTravelLetter::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Surat Resmi';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Perjalanan Dinas';
    protected static ?string $modelLabel = 'Surat Perjalanan Dinas';
    protected static ?string $pluralModelLabel = 'Surat Perjalanan Dinas';

    public static function getEloquentQuery(): Builder
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
        $employeeId = $employee->id;
        
        return parent::getEloquentQuery()
            ->where(function (Builder $query) use ($employeeId) {
                $query->where('employee_id', $employeeId)
                      ->orWhereJsonContains('additional_employee_ids', (string)$employeeId)
                      ->orWhereJsonContains('additional_employee_ids', (int)$employeeId);
            });
    }

    public static function canCreate(): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Perjalanan Dinas')
                    ->schema([
                        Forms\Components\TextEntry::make('registration_number')->label('Nomor Surat'),
                        Forms\Components\TextEntry::make('destination')->label('Tujuan'),
                        Forms\Components\TextEntry::make('purpose_of_trip')->label('Maksud Perjalanan')->columnSpanFull(),
                        Forms\Components\TextEntry::make('start_date')->label('Berangkat')->date(),
                        Forms\Components\TextEntry::make('end_date')->label('Kembali')->date(),
                        Forms\Components\TextEntry::make('total_cost')->label('Total Biaya')->money('IDR'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')->label('Nomor Surat')->searchable(),
                Tables\Columns\TextColumn::make('destination')->label('Tujuan'),
                Tables\Columns\TextColumn::make('start_date')->label('Berangkat')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('end_date')->label('Kembali')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('total_cost')->label('Biaya')->money('IDR'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn(EmployeeBusinessTravelLetter $record) => $record->pdf_file_path ? asset('storage/' . $record->pdf_file_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn(EmployeeBusinessTravelLetter $record) => !empty($record->pdf_file_path)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeBusinessTravelLetters::route('/'),
        ];
    }
}
