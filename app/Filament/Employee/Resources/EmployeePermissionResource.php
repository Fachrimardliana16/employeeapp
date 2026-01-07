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
use Illuminate\Support\Facades\Auth;

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
                    ->description('Pilih Pegawai yang akan mengajukan izin/cuti/resign')
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
                Forms\Components\Section::make('Detail Izin/Cuti')
                    ->description('Lengkapi informasi detail izin atau cuti')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\Select::make('permission_id')
                            ->label('Jenis Izin/Cuti')
                            ->relationship('permission', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih jenis izin/cuti...'),
                        Forms\Components\DatePicker::make('start_permission_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->native(false)
                            ->placeholder('Pilih tanggal mulai...'),
                        Forms\Components\DatePicker::make('end_permission_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->native(false)
                            ->placeholder('Pilih tanggal selesai...'),
                        Forms\Components\Textarea::make('permission_desc')
                            ->label('Alasan/Deskripsi')
                            ->required()
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Jelaskan alasan izin/cuti...'),
                        Forms\Components\FileUpload::make('scan_doc')
                            ->label('Dokumen Pendukung')
                            ->directory('permissions')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->helperText('Format: PDF atau gambar. Maksimal 5MB.')
                            ->maxSize(5120),
                    ]),
                Forms\Components\Section::make('Persetujuan')
                    ->description('Status dan detail persetujuan')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Forms\Components\Select::make('approval_status')
                            ->label('Status Persetujuan')
                            ->options([
                                'pending' => 'Menunggu Persetujuan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih penyetuju...')
                            ->visible(fn(Forms\Get $get) => $get('approval_status') !== 'pending'),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Tanggal Persetujuan')
                            ->native(false)
                            ->visible(fn(Forms\Get $get) => $get('approval_status') !== 'pending'),
                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Catatan Persetujuan')
                            ->rows(2)
                            ->placeholder('Catatan dari penyetuju...')
                            ->visible(fn(Forms\Get $get) => $get('approval_status') !== 'pending'),
                        Forms\Components\Hidden::make('users_id')
                            ->default(Auth::id()),
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
                Tables\Columns\TextColumn::make('permission.name')
                    ->label('Jenis Izin/Cuti')
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('start_permission_date')
                    ->label('Tanggal Mulai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_permission_date')
                    ->label('Tanggal Selesai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('approval_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->sortable()
                    ->placeholder('Belum ada'),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Tgl Persetujuan')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Belum disetujui')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('scan_doc')
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('approval_status')
                    ->label('Status Persetujuan')
                    ->options([
                        'pending' => 'Menunggu Persetujuan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->placeholder('Semua Status'),
                Tables\Filters\SelectFilter::make('permission_id')
                    ->label('Jenis Izin/Cuti')
                    ->relationship('permission', 'name')
                    ->placeholder('Semua Jenis'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(EmployeePermission $record) => $record->approval_status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Catatan Persetujuan')
                            ->rows(3)
                            ->placeholder('Catatan (opsional)...'),
                    ])
                    ->action(function (EmployeePermission $record, array $data) {
                        $record->update([
                            'approval_status' => 'approved',
                            'approved_by' => \Illuminate\Support\Facades\Auth::id(),
                            'approved_at' => now(),
                            'approval_notes' => $data['approval_notes'] ?? null,
                        ]);
                    })
                    ->successNotificationTitle('Berhasil disetujui'),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(EmployeePermission $record) => $record->approval_status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3)
                            ->placeholder('Jelaskan alasan penolakan...'),
                    ])
                    ->action(function (EmployeePermission $record, array $data) {
                        $record->update([
                            'approval_status' => 'rejected',
                            'approved_by' => \Illuminate\Support\Facades\Auth::id(),
                            'approved_at' => now(),
                            'approval_notes' => $data['approval_notes'],
                        ]);
                    })
                    ->successNotificationTitle('Berhasil ditolak'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
