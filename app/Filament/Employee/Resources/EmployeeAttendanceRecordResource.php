<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeAttendanceRecordResource\Pages;
use App\Filament\Employee\Resources\EmployeeAttendanceRecordResource\RelationManagers;
use App\Models\EmployeeAttendanceRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeAttendanceRecordResource extends Resource
{
    protected static ?string $model = EmployeeAttendanceRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Absensi & Kehadiran';

    protected static ?string $navigationLabel = 'Kehadiran';

    protected static ?string $modelLabel = 'Catatan Kehadiran';

    protected static ?string $pluralModelLabel = 'Catatan Kehadiran';

    protected static ?int $navigationSort = 501;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('pin')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('employee_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('attendance_time')
                    ->required(),
                Forms\Components\TextInput::make('state')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('verification')
                    ->maxLength(255),
                Forms\Components\TextInput::make('work_code')
                    ->maxLength(255),
                Forms\Components\TextInput::make('reserved')
                    ->maxLength(255),
                Forms\Components\TextInput::make('device')
                    ->maxLength(255),
                Forms\Components\TextInput::make('picture')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('employee_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('attendance_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state')
                    ->searchable(),
                Tables\Columns\TextColumn::make('verification')
                    ->searchable(),
                Tables\Columns\TextColumn::make('work_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reserved')
                    ->searchable(),
                Tables\Columns\TextColumn::make('device')
                    ->searchable(),
                Tables\Columns\TextColumn::make('picture')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeAttendanceRecords::route('/'),
            'create' => Pages\CreateEmployeeAttendanceRecord::route('/create'),
            'edit' => Pages\EditEmployeeAttendanceRecord::route('/{record}/edit'),
        ];
    }
}
