<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeFamilyResource\Pages;
use App\Filament\Employee\Resources\EmployeeFamilyResource\RelationManagers;
use App\Models\EmployeeFamily;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeFamilyResource extends Resource
{
    protected static ?string $model = EmployeeFamily::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Manajemen Pegawai';

    protected static ?string $navigationLabel = 'Data Keluarga';

    protected static ?string $modelLabel = 'Data Keluarga';

    protected static ?string $pluralModelLabel = 'Data Keluarga';

    protected static ?int $navigationSort = 102;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Keluarga')
                    ->description('Kelola data keluarga Pegawai')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Forms\Components\Select::make('employees_id')
                            ->label('Pegawai')
                            ->relationship('employee', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih Pegawai...'),
                        Forms\Components\Select::make('master_employee_families_id')
                            ->label('Hubungan Keluarga')
                            ->relationship('masterFamily', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih hubungan keluarga...'),
                        Forms\Components\TextInput::make('family_name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama lengkap anggota keluarga...'),
                        Forms\Components\Select::make('family_gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'male' => 'Laki-laki',
                                'female' => 'Perempuan',
                            ])
                            ->required()
                            ->placeholder('Pilih jenis kelamin...'),
                        Forms\Components\TextInput::make('family_id_number')
                            ->label('Nomor KTP')
                            ->maxLength(20)
                            ->placeholder('Masukkan nomor KTP (opsional)...'),
                        Forms\Components\TextInput::make('family_place_birth')
                            ->label('Tempat Lahir')
                            ->maxLength(255)
                            ->placeholder('Masukkan tempat lahir...'),
                        Forms\Components\DatePicker::make('family_date_birth')
                            ->label('Tanggal Lahir')
                            ->native(false)
                            ->placeholder('Pilih tanggal lahir...'),
                        Forms\Components\Textarea::make('family_address')
                            ->label('Alamat')
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Masukkan alamat lengkap...'),
                        Forms\Components\TextInput::make('family_phone')
                            ->label('Nomor Telepon')
                            ->maxLength(20)
                            ->placeholder('Masukkan nomor telepon...'),
                        Forms\Components\Toggle::make('is_emergency_contact')
                            ->label('Kontak Darurat')
                            ->helperText('Centang jika anggota keluarga ini adalah kontak darurat')
                            ->default(false),
                        Forms\Components\Hidden::make('users_id')
                            ->default(fn () => auth()->id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Pegawai')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('masterFamily.name')
                    ->label('Hubungan Keluarga')
                    ->sortable(),
                Tables\Columns\TextColumn::make('family_name')
                    ->label('Nama Anggota Keluarga')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('family_gender')
                    ->label('Jenis Kelamin')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('family_id_number')
                    ->label('Nomor KTP')
                    ->searchable()
                    ->placeholder('Tidak ada'),
                Tables\Columns\TextColumn::make('family_date_birth')
                    ->label('Tanggal Lahir')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Tidak ada'),
                Tables\Columns\IconColumn::make('is_emergency_contact')
                    ->label('Kontak Darurat')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Tanggal Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('family_gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ])
                    ->placeholder('Semua Jenis Kelamin'),
                Tables\Filters\SelectFilter::make('master_employee_families_id')
                    ->relationship('masterFamily', 'name')
                    ->label('Hubungan Keluarga')
                    ->placeholder('Semua Hubungan'),
                Tables\Filters\TernaryFilter::make('is_emergency_contact')
                    ->label('Kontak Darurat')
                    ->placeholder('Semua Status')
                    ->trueLabel('Hanya Kontak Darurat')
                    ->falseLabel('Bukan Kontak Darurat')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail')
                    ->modalHeading('Detail Data Keluarga'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah Data')
                    ->modalHeading('Ubah Data Keluarga'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Data Keluarga')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data keluarga ini?')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih')
                        ->modalHeading('Hapus Data Keluarga yang Dipilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua data keluarga yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->modalCancelActionLabel('Batal'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Data Keluarga')
            ->emptyStateDescription('Mulai dengan menambahkan data keluarga Pegawai pertama.')
            ->emptyStateIcon('heroicon-o-users');
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
            'index' => Pages\ListEmployeeFamilies::route('/'),
            'create' => Pages\CreateEmployeeFamily::route('/create'),
            'edit' => Pages\EditEmployeeFamily::route('/{record}/edit'),
        ];
    }
}
