<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeTrainingResource\Pages;
use App\Filament\Employee\Resources\EmployeeTrainingResource\RelationManagers;
use App\Models\EmployeeTraining;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeTrainingResource extends Resource
{
    protected static ?string $model = EmployeeTraining::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Kinerja & Pengembangan';

    protected static ?string $navigationLabel = 'Pelatihan';

    protected static ?string $modelLabel = 'Pelatihan Pegawai';

    protected static ?string $pluralModelLabel = 'Pelatihan Pegawai';

    protected static ?int $navigationSort = 602;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pegawai')
                    ->description('Pilih Pegawai yang mengikuti pelatihan')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai')
                            ->relationship('employee', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih Pegawai...'),
                    ]),
                Forms\Components\Section::make('Detail Pelatihan')
                    ->description('Informasi lengkap tentang pelatihan yang diikuti')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Forms\Components\DatePicker::make('training_date')
                            ->label('Tanggal Pelatihan')
                            ->required()
                            ->native(false)
                            ->placeholder('Pilih tanggal pelatihan...'),
                        Forms\Components\TextInput::make('training_title')
                            ->label('Judul Pelatihan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Workshop Digital Marketing...'),
                        Forms\Components\TextInput::make('training_location')
                            ->label('Lokasi Pelatihan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Hotel Grand Indonesia, Jakarta...'),
                        Forms\Components\TextInput::make('organizer')
                            ->label('Penyelenggara')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: PT Training Indonesia...'),
                        Forms\Components\FileUpload::make('photo_training')
                            ->label('Foto Pelatihan')
                            ->directory('training-photos')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->helperText('Format: JPG, PNG. Rasio 16:9 untuk hasil terbaik.'),
                        Forms\Components\FileUpload::make('docs_training')
                            ->label('Dokumen Pelatihan')
                            ->directory('training-docs')
                            ->acceptedFileTypes(['pdf', 'doc', 'docx'])
                            ->helperText('Format: PDF, DOC, DOCX. Maksimal 5MB.')
                            ->maxSize(5120),
                        Forms\Components\Hidden::make('users_id')
                            ->default(fn() => auth()->id() ?? 0),
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
                Tables\Columns\TextColumn::make('training_date')
                    ->label('Tanggal Pelatihan')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('training_title')
                    ->label('Judul Pelatihan')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(50),
                Tables\Columns\TextColumn::make('training_location')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('organizer')
                    ->label('Penyelenggara')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\ImageColumn::make('photo_training')
                    ->label('Foto')
                    ->square()
                    ->size(50),
                Tables\Columns\IconColumn::make('docs_training')
                    ->label('Dokumen')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document')
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
                Tables\Filters\Filter::make('training_date')
                    ->label('Tanggal Pelatihan')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->placeholder('Pilih tanggal mulai'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->placeholder('Pilih tanggal akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('training_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('training_date', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('has_docs')
                    ->label('Dengan Dokumen')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('docs_training'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat')
                        ->modalHeading('Detail Pelatihan Pegawai'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->modalHeading('Ubah Data Pelatihan'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Data Pelatihan')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data pelatihan ini?')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih')
                        ->modalHeading('Hapus Data Pelatihan yang Dipilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua data pelatihan yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->modalCancelActionLabel('Batal'),
                ])->label('Hapus yang Dipilih'),
            ])
            ->emptyStateHeading('Belum Ada Data Pelatihan')
            ->emptyStateDescription('Mulai dengan menambahkan data pelatihan Pegawai pertama.')
            ->emptyStateIcon('heroicon-o-academic-cap');
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
            'index' => Pages\ListEmployeeTrainings::route('/'),
            'create' => Pages\CreateEmployeeTraining::route('/create'),
            'edit' => Pages\EditEmployeeTraining::route('/{record}/edit'),
        ];
    }
}
