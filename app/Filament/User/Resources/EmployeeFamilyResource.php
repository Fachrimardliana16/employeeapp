<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\EmployeeFamilyResource\Pages;
use App\Models\Employee;
use App\Models\EmployeeFamily;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EmployeeFamilyResource extends Resource
{
    protected static ?string $model = EmployeeFamily::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Data Mandiri';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Data Keluarga';
    protected static ?string $modelLabel = 'Data Keluarga';
    protected static ?string $pluralModelLabel = 'Data Keluarga';

    public static function getEloquentQuery(): Builder
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
        return parent::getEloquentQuery()->where('employees_id', $employee->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Keluarga')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('master_employee_families_id')
                                    ->label('Hubungan Keluarga')
                                    ->relationship('masterFamily', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('family_name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('family_gender')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'male' => 'Laki-laki',
                                        'female' => 'Perempuan',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('family_id_number')
                                    ->label('Nomor KTP (NIP)')
                                    ->maxLength(20),
                            ]),
                        Forms\Components\Textarea::make('family_address')
                            ->label('Alamat')
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('family_place_birth')
                                    ->label('Tempat Lahir')
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('family_date_birth')
                                    ->label('Tanggal Lahir')
                                    ->native(false),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('family_phone')
                                    ->label('Nomor Telepon')
                                    ->maxLength(20),
                                Forms\Components\Toggle::make('is_emergency_contact')
                                    ->label('Kontak Darurat')
                                    ->default(false),
                            ]),
                        Forms\Components\Hidden::make('employees_id')
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
                Tables\Columns\TextColumn::make('masterFamily.name')
                    ->label('Hubungan Keluarga'),
                Tables\Columns\TextColumn::make('family_name')
                    ->label('Nama Anggota Keluarga')
                    ->searchable(),
                Tables\Columns\TextColumn::make('family_gender')
                    ->label('Jenis Kelamin')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'success',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_emergency_contact')
                    ->label('Kontak Darurat')
                    ->boolean(),
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
            'index' => Pages\ListEmployeeFamilies::route('/'),
            'create' => Pages\CreateEmployeeFamily::route('/create'),
            'edit' => Pages\EditEmployeeFamily::route('/{record}/edit'),
        ];
    }
}
