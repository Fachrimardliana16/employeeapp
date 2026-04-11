<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\PerformanceAppraisalResource\Pages;
use App\Filament\Employee\Resources\PerformanceAppraisalResource\RelationManagers;
use App\Models\PerformanceAppraisal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PerformanceAppraisalResource extends Resource
{
    protected static ?string $model = PerformanceAppraisal::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Kinerja & Pengembangan';
    protected static ?string $navigationLabel = 'Penilaian Kinerja';
    protected static ?string $modelLabel = 'Penilaian Kinerja';
    protected static ?string $pluralModelLabel = 'Penilaian Kinerja';
    protected static ?int $navigationSort = 601;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Penilaian')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai')
                            ->relationship('employee', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('appraisal_period')
                            ->label('Periode Penilaian')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('appraisal_date')
                            ->label('Tanggal Penilaian')
                            ->required(),
                        Forms\Components\Select::make('appraiser_id')
                            ->label('Penilai')
                            ->relationship('appraiser', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('criteria_scores')
                            ->label('Skor Kriteria')
                            ->required(),
                        Forms\Components\TextInput::make('total_score')
                            ->label('Total Skor')
                            ->required()
                            ->numeric()
                            ->default(0.00),
                        Forms\Components\TextInput::make('performance_grade')
                            ->label('Nilai Kinerja'),
                        Forms\Components\Textarea::make('strengths')
                            ->label('Kelebihan')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('weaknesses')
                            ->label('Kekurangan')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('recommendations')
                            ->label('Rekomendasi')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('employee_comment')
                            ->label('Komentar Pegawai')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Pegawai')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('appraisal_period')
                    ->label('Periode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('appraisal_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('appraiser.name')
                    ->label('Penilai')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_score')
                    ->label('Total Skor')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('performance_grade')
                    ->label('Nilai Kinerja'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status'),
                Tables\Columns\TextColumn::make('approved_by_user.name')
                    ->label('Disetujui Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Disetujui Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->label('Hapus yang Dipilih'),
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
            'index' => Pages\ListPerformanceAppraisals::route('/'),
            'create' => Pages\CreatePerformanceAppraisal::route('/create'),
            'edit' => Pages\EditPerformanceAppraisal::route('/{record}/edit'),
        ];
    }
}
