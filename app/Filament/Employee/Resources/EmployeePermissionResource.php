<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeePermissionResource\Pages;
use App\Filament\Employee\Resources\EmployeePermissionResource\RelationManagers;
use App\Models\EmployeePermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeePermissionResource extends Resource
{
    protected static ?string $model = EmployeePermission::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Absensi & Kehadiran';

    protected static ?string $navigationLabel = 'Izin & Cuti';

    protected static ?string $modelLabel = 'Izin & Cuti';

    protected static ?string $pluralModelLabel = 'Izin & Cuti';

    protected static ?int $navigationSort = 502;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pegawai')
                    ->description('Pilih Pegawai yang akan mengajukan izin/cuti')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Select::make('employees_id')
                            ->label('Pegawai')
                            ->relationship('employee', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih Pegawai...'),
                    ]),
                Forms\Components\Section::make('Detail Izin/Cuti')
                    ->description('Lengkapi informasi detail izin atau cuti')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\Select::make('master_employee_permissions_id')
                            ->label('Jenis Izin/Cuti')
                            ->relationship('masterPermission', 'permission_type_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih jenis izin/cuti...'),
                        Forms\Components\DatePicker::make('permission_start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->native(false)
                            ->placeholder('Pilih tanggal mulai...')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                $start = $state;
                                $end = $get('permission_end_date');
                                if ($start && $end) {
                                    $days = \Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) + 1;
                                    $set('permission_duration_days', $days);
                                }
                            }),
                        Forms\Components\DatePicker::make('permission_end_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->native(false)
                            ->placeholder('Pilih tanggal selesai...')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                $start = $get('permission_start_date');
                                $end = $state;
                                if ($start && $end) {
                                    $days = \Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) + 1;
                                    $set('permission_duration_days', $days);
                                }
                            }),
                        Forms\Components\TextInput::make('permission_duration_days')
                            ->label('Durasi (Hari)')
                            ->required()
                            ->numeric()
                            ->suffix('hari')
                            ->readOnly()
                            ->helperText('Dihitung otomatis berdasarkan tanggal mulai dan selesai'),
                        Forms\Components\Textarea::make('permission_reason')
                            ->label('Alasan Izin/Cuti')
                            ->required()
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Jelaskan alasan mengajukan izin/cuti...'),
                        Forms\Components\Textarea::make('permission_description')
                            ->label('Deskripsi Tambahan')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Informasi tambahan (opsional)...'),
                        Forms\Components\FileUpload::make('permission_file')
                            ->label('Dokumen Pendukung')
                            ->directory('permissions')
                            ->acceptedFileTypes(['pdf', 'doc', 'docx', 'jpg', 'png'])
                            ->helperText('Format: PDF, DOC, DOCX, JPG, PNG. Maksimal 5MB.')
                            ->maxSize(5120),
                        Forms\Components\Select::make('permission_status')
                            ->label('Status Persetujuan')
                            ->options([
                                'pending' => 'Menunggu Persetujuan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\DatePicker::make('approval_date')
                            ->label('Tanggal Persetujuan')
                            ->native(false),
                        Forms\Components\Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih penyetuju...'),
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
                Tables\Columns\TextColumn::make('masterPermission.permission_type_name')
                    ->label('Jenis Izin/Cuti')
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('permission_start_date')
                    ->label('Tanggal Mulai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('permission_end_date')
                    ->label('Tanggal Selesai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('permission_duration_days')
                    ->label('Durasi')
                    ->suffix(' hari')
                    ->sortable(),
                Tables\Columns\TextColumn::make('permission_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu Persetujuan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->sortable()
                    ->placeholder('Belum ada'),
                Tables\Columns\IconColumn::make('permission_file')
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
                Tables\Filters\SelectFilter::make('permission_status')
                    ->label('Status Persetujuan')
                    ->options([
                        'pending' => 'Menunggu Persetujuan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->placeholder('Semua Status'),
                Tables\Filters\SelectFilter::make('master_employee_permissions_id')
                    ->label('Jenis Izin/Cuti')
                    ->relationship('masterPermission', 'permission_type_name')
                    ->placeholder('Semua Jenis'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail')
                    ->modalHeading('Detail Izin/Cuti'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah Data')
                    ->modalHeading('Ubah Data Izin/Cuti'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Data Izin/Cuti')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data izin/cuti ini?')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih')
                        ->modalHeading('Hapus Data Izin/Cuti yang Dipilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua data izin/cuti yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->modalCancelActionLabel('Batal'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Data Izin/Cuti')
            ->emptyStateDescription('Mulai dengan menambahkan data izin atau cuti Pegawai pertama.')
            ->emptyStateIcon('heroicon-o-calendar-days');
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
            'index' => Pages\ListEmployeePermissions::route('/'),
            'create' => Pages\CreateEmployeePermission::route('/create'),
            'edit' => Pages\EditEmployeePermission::route('/{record}/edit'),
        ];
    }
}
