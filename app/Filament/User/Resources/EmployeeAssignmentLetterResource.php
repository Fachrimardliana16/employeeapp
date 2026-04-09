<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\EmployeeAssignmentLetterResource\Pages;
use App\Models\EmployeeAssignmentLetter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EmployeeAssignmentLetterResource extends Resource
{
    protected static ?string $model = EmployeeAssignmentLetter::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Surat Resmi';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Surat Tugas';
    protected static ?string $modelLabel = 'Surat Tugas';
    protected static ?string $pluralModelLabel = 'Surat Tugas';

    public static function getEloquentQuery(): Builder
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
        $employeeId = $employee->id;
        
        return parent::getEloquentQuery()
            ->where(function (Builder $query) use ($employeeId) {
                $query->where('assigning_employee_id', $employeeId)
                      ->orWhereJsonContains('additional_employee_ids', (string)$employeeId)
                      ->orWhereJsonContains('additional_employee_ids', (int)$employeeId);
            });
    }

    public static function canCreate(): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Surat Tugas')
                    ->schema([
                        Forms\Components\TextEntry::make('registration_number')->label('Nomor Surat'),
                        Forms\Components\TextEntry::make('task')->label('Tugas')->columnSpanFull(),
                        Forms\Components\TextEntry::make('start_date')->label('Mulai')->date(),
                        Forms\Components\TextEntry::make('end_date')->label('Selesai')->date(),
                        Forms\Components\TextEntry::make('signatory_name')->label('Penandatangan'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')->label('Nomor Surat')->searchable(),
                Tables\Columns\TextColumn::make('task')->label('Tugas')->limit(50),
                Tables\Columns\TextColumn::make('start_date')->label('Mulai')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('end_date')->label('Selesai')->date('d/m/Y'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn(EmployeeAssignmentLetter $record) => $record->pdf_file_path ? asset('storage/' . $record->pdf_file_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn(EmployeeAssignmentLetter $record) => !empty($record->pdf_file_path)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeAssignmentLetters::route('/'),
        ];
    }
}
