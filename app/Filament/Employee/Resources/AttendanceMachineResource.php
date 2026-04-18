<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\AttendanceMachineResource\Pages;
use App\Filament\Employee\Resources\AttendanceMachineResource\RelationManagers;
use App\Models\AttendanceMachine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendanceMachineResource extends Resource
{
    protected static ?string $model = AttendanceMachine::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Absensi & Kehadiran';

    protected static ?string $modelLabel = 'Mesin Absensi';

    protected static ?string $pluralModelLabel = 'Mesin Absensi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Mesin')
                    ->schema([
                        Forms\Components\TextInput::make('serial_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label('Serial Number (SN)'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Nama Mesin'),
                        Forms\Components\Select::make('master_office_location_id')
                            ->relationship('officeLocation', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Lokasi Kantor'),
                        Forms\Components\Placeholder::make('last_heard_at')
                            ->content(fn ($record) => $record?->last_heard_at?->diffForHumans() ?? '-')
                            ->label('Terakhir Aktif'),
                        Forms\Components\Placeholder::make('ip_address')
                            ->content(fn ($record) => $record?->ip_address ?? '-')
                            ->label('Alamat IP'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Mesin'),
                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable()
                    ->sortable()
                    ->label('SN'),
                Tables\Columns\TextColumn::make('officeLocation.name')
                    ->searchable()
                    ->sortable()
                    ->label('Lokasi Kantor'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($record): string => match (true) {
                        $record->is_online => 'success',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($record): string => $record->is_online ? 'Online' : 'Offline')
                    ->label('Status'),
                Tables\Columns\TextColumn::make('last_heard_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Terakhir Aktif'),
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
            'index' => Pages\ListAttendanceMachines::route('/'),
            'create' => Pages\CreateAttendanceMachine::route('/create'),
            'edit' => Pages\EditAttendanceMachine::route('/{record}/edit'),
        ];
    }
}
