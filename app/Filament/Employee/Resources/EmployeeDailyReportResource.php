<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeDailyReportResource\Pages;
use App\Filament\Employee\Resources\EmployeeDailyReportResource\RelationManagers;
use App\Models\EmployeeDailyReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;

class EmployeeDailyReportResource extends Resource
{
    protected static ?string $model = EmployeeDailyReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    protected static ?string $navigationGroup = 'Absensi & Kehadiran';

    protected static ?string $navigationLabel = 'Laporan Harian';

    protected static ?string $modelLabel = 'Laporan Harian';

    protected static ?string $pluralModelLabel = 'Laporan Harian';

    protected static ?int $navigationSort = 503;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('daily_report_date')
                    ->required()
                    ->maxDate(now()),
                Forms\Components\Textarea::make('work_description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('work_status')
                    ->label('Status Kerja')
                    ->options([
                        'completed' => 'Selesai',
                        'in_progress' => 'Proses',
                        'pending' => 'Tertunda',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->required()
                    ->default('completed'),
                Forms\Components\Textarea::make('desc')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->imageEditor()
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('1024')
                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                    ->optimize('webp'),
                Forms\Components\Hidden::make('users_id')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('daily_report_date')
                    ->label('Tanggal Laporan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_status')
                    ->label('Status Kerja')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'warning',
                        'pending' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'completed' => 'Selesai',
                        'in_progress' => 'Proses',
                        'pending' => 'Tertunda',
                        'cancelled' => 'Batal',
                        default => $state,
                    }),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Gambar'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Pegawai')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('work_status')
                    ->label('Status Kerja')
                    ->options([
                        'completed' => 'Selesai',
                        'in_progress' => 'Proses',
                        'pending' => 'Tertunda',
                        'cancelled' => 'Batal',
                    ]),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ])->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
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

    public static function canCreate(): bool
    {
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeDailyReports::route('/'),
            'create' => Pages\CreateEmployeeDailyReport::route('/create'),
            'edit' => Pages\EditEmployeeDailyReport::route('/{record}/edit'),
        ];
    }
}
