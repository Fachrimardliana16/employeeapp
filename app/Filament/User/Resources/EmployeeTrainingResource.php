<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\EmployeeTrainingResource\Pages;
use App\Models\EmployeeTraining;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EmployeeTrainingResource extends Resource
{
    protected static ?string $model = EmployeeTraining::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Data Mandiri';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Pelatihan';
    protected static ?string $modelLabel = 'Pelatihan';
    protected static ?string $pluralModelLabel = 'Pelatihan';

    public static function getEloquentQuery(): Builder
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
        return parent::getEloquentQuery()->where('employee_id', $employee->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pelatihan')
                    ->schema([
                        Forms\Components\DatePicker::make('training_date')
                            ->label('Tanggal Pelatihan')
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('training_title')
                            ->label('Judul Pelatihan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('training_location')
                            ->label('Lokasi Pelatihan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('organizer')
                            ->label('Penyelenggara')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('photo_training')
                            ->label('Foto Pelatihan')
                            ->directory('training-photos')
                            ->image()
                            ->imageEditor()
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth(1024)
                            ->optimize('webp')
                            ->maxSize(15360),
                        Forms\Components\FileUpload::make('docs_training')
                            ->label('Dokumen Pelatihan')
                            ->directory('training-docs')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
                        Forms\Components\Hidden::make('employee_id')
                            ->default(fn() => Auth::user()->employee?->id),
                        Forms\Components\Hidden::make('users_id')
                            ->default(fn() => auth()->id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('training_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('training_title')
                    ->label('Judul Pelatihan')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('organizer')
                    ->label('Penyelenggara'),
                Tables\Columns\IconColumn::make('docs_training')
                    ->label('Dokumen')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeTrainings::route('/'),
            'create' => Pages\CreateEmployeeTraining::route('/create'),
            'edit' => Pages\EditEmployeeTraining::route('/{record}/edit'),
        ];
    }
}
