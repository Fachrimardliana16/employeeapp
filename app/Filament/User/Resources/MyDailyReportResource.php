<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\MyDailyReportResource\Pages;
use App\Models\EmployeeDailyReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyDailyReportResource extends Resource
{
    protected static ?string $model = EmployeeDailyReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Presensi & Laporan';
    protected static ?string $navigationLabel = 'Laporan Harian';
    protected static ?string $modelLabel = 'Laporan Harian';
    protected static ?string $pluralModelLabel = 'Laporan Harian';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $employee = \App\Models\Employee::where('email', $user->email)->first();

        if (!$employee) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()->where('employee_id', $employee->id);
    }

    public static function canCreate(): bool
    {
        return true;
    }
    public static function canEdit($record): bool
    {
        return true;
    }
    public static function canDelete($record): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Laporan Kerja Harian')
                    ->schema([
                        Forms\Components\DatePicker::make('report_date')
                            ->label('Tanggal')
                            ->required()
                            ->default(now())
                            ->native(false),

                        Forms\Components\Textarea::make('report_content')
                            ->label('Isi Laporan')
                            ->required()
                            ->rows(5)
                            ->placeholder('Tuliskan aktivitas dan pencapaian hari ini...')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_date')->label('Tanggal')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('report_content')->label('Laporan')->limit(50)->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('report_date', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),
                    Tables\Actions\EditAction::make()->label('Edit'),
                    Tables\Actions\DeleteAction::make()->label('Hapus'),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMyDailyReports::route('/'),
            'create' => Pages\CreateMyDailyReport::route('/create'),
            'view' => Pages\ViewMyDailyReport::route('/{record}'),
            'edit' => Pages\EditMyDailyReport::route('/{record}/edit'),
        ];
    }
}
