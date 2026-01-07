<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeRetirementResource\Pages;
use App\Filament\Employee\Resources\EmployeeRetirementResource\RelationManagers;
use App\Models\EmployeeRetirement;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class EmployeeRetirementResource extends Resource
{
    protected static ?string $model = EmployeeRetirement::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Operasional Pegawai';

    protected static ?string $navigationLabel = 'Pensiun Pegawai';

    protected static ?string $modelLabel = 'Pensiun Pegawai';

    protected static ?string $pluralModelLabel = 'Pensiun Pegawai';

    protected static ?int $navigationSort = 304;

    public static function getModelLabel(): string
    {
        return 'Pensiun Pegawai';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pensiun Pegawai';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Formulir Pensiun Pegawai')
                    ->description('Lengkapi informasi pensiun Pegawai dengan teliti')
                    ->icon('heroicon-o-home')
                    ->schema([
                        Forms\Components\Tabs::make('Informasi Pensiun')
                            ->tabs([
                        Forms\Components\Tabs\Tab::make('Informasi Pegawai')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Select::make('employee_id')
                                    ->label('Pegawai')
                                    ->relationship('employee', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->placeholder('Pilih Pegawai...')
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if ($state) {
                                            $employee = Employee::find($state);
                                            if ($employee) {
                                                $set('employee_number', $employee->nippam);
                                                $set('current_position', $employee->position->name ?? '');
                                                $set('current_department', $employee->department->name ?? '');
                                                $set('current_grade', $employee->grade->name ?? '');
                                                $set('hire_date', $employee->entry_date);
                                            }
                                        }
                                    }),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('employee_number')
                                            ->label('Nomor Pegawai')
                                            ->disabled()
                                            ->dehydrated(false),

                                        Forms\Components\TextInput::make('current_position')
                                            ->label('Posisi Saat Ini')
                                            ->disabled()
                                            ->dehydrated(false),

                                        Forms\Components\TextInput::make('current_department')
                                            ->label('Departemen Saat Ini')
                                            ->disabled()
                                            ->dehydrated(false),

                                        Forms\Components\TextInput::make('current_grade')
                                            ->label('Grade Saat Ini')
                                            ->disabled()
                                            ->dehydrated(false),

                                        Forms\Components\DatePicker::make('hire_date')
                                            ->label('Tanggal Masuk')
                                            ->disabled()
                                            ->dehydrated(false),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Detail Pensiun')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DatePicker::make('retirement_date')
                                            ->label('Tanggal Pensiun')
                                            ->required()
                                            ->minDate(now())
                                            ->placeholder('Pilih tanggal pensiun...')
                                            ->helperText('Tanggal efektif pensiun Pegawai')
                                            ->native(false),
                                    ]),

                                Forms\Components\Textarea::make('reason')
                                    ->label('Alasan Pensiun')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->placeholder('Jelaskan alasan pensiun secara detail...')
                                    ->helperText('Berikan penjelasan lengkap mengenai alasan pensiun Pegawai')
                                    ->maxLength(1000)
                                    ->required(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Dokumen')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\FileUpload::make('docs')
                                    ->label('Dokumen Pensiun')
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->maxSize(5120)
                                    ->directory('retirement-documents')
                                    ->visibility('private')
                                    ->columnSpanFull()
                                    ->helperText('Format yang didukung: PDF, JPEG, PNG. Maksimal 5MB.')
                                    ->placeholder('Unggah dokumen pensiun...'),
                            ]),
                        ])
                        ->columnSpanFull()
                        ->persistTabInQueryString(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.nippam')
                    ->label('NIPPAM')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('employee.department.name')
                    ->label('Departemen')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('employee.position.name')
                    ->label('Posisi')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('retirement_date')
                    ->label('Tanggal Pensiun')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan Pensiun')
                    ->limit(50)
                    ->tooltip(function (EmployeeRetirement $record): ?string {
                        return $record->reason;
                    })
                    ->wrap(),

                Tables\Columns\IconColumn::make('docs')
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
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Tanggal Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->label('Departemen')
                    ->relationship('employee.department', 'name')
                    ->placeholder('Semua Departemen'),

                Tables\Filters\Filter::make('retirement_date')
                    ->label('Tanggal Pensiun')
                    ->form([
                        Forms\Components\DatePicker::make('retirement_from')
                            ->label('Dari Tanggal')
                            ->placeholder('Pilih tanggal mulai'),
                        Forms\Components\DatePicker::make('retirement_until')
                            ->label('Sampai Tanggal')
                            ->placeholder('Pilih tanggal akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['retirement_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('retirement_date', '>=', $date),
                            )
                            ->when(
                                $data['retirement_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('retirement_date', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('has_docs')
                    ->label('Dengan Dokumen')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('docs'))
                    ->toggle(),

                Tables\Filters\Filter::make('recent_retirements')
                    ->label('Pensiun Bulan Ini')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('retirement_date', now()->month))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail')
                    ->modalHeading('Detail Pensiun Pegawai')
                    ->modalWidth('4xl'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah Data')
                    ->modalHeading('Ubah Data Pensiun')
                    ->modalWidth('4xl'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Data Pensiun')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data pensiun ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih')
                        ->modalHeading('Hapus Data Pensiun yang Dipilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua data pensiun yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->modalCancelActionLabel('Batal'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum Ada Data Pensiun')
            ->emptyStateDescription('Mulai dengan menambahkan data pensiun Pegawai pertama.')
            ->emptyStateIcon('heroicon-o-home');
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
            'index' => Pages\ListEmployeeRetirements::route('/'),
            'create' => Pages\CreateEmployeeRetirement::route('/create'),
            'edit' => Pages\EditEmployeeRetirement::route('/{record}/edit'),
        ];
    }
}
