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
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;

class EmployeePermissionResource extends Resource
{
    protected static ?string $model = EmployeePermission::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Absensi & Kehadiran';

    protected static ?string $navigationLabel = 'Izin & Cuti';

    protected static ?string $modelLabel = 'Izin & Cuti';

    protected static ?string $pluralModelLabel = 'Izin & Cuti';

    protected static ?int $navigationSort = 502;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('approval_status', 'pending')->count();
            
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

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
                            ->placeholder('Pilih tanggal selesai...')
                            ->afterOrEqual('start_permission_date')
                            ->rules([
                                fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $employeeId = $get('employee_id');
                                    $start = $get('start_permission_date');
                                    $end = $value;
                                    $recordId = $get('../id'); // Filament v3 way to get current record ID in form

                                    if (!$employeeId || !$start || !$end) return;

                                    $overlap = \App\Models\EmployeePermission::where('employee_id', $employeeId)
                                        ->where('approval_status', '!=', 'rejected')
                                        ->when($recordId, fn($q) => $q->where('id', '!=', $recordId))
                                        ->where(function ($query) use ($start, $end) {
                                            $query->whereBetween('start_permission_date', [$start, $end])
                                                ->orWhereBetween('end_permission_date', [$start, $end])
                                                ->orWhere(fn($q) => $q->where('start_permission_date', '<=', $start)->where('end_permission_date', '>=', $end));
                                        })
                                        ->exists();

                                    if ($overlap) {
                                        $fail('Pegawai sudah memiliki pengajuan izin/cuti pada rentang tanggal tersebut.');
                                    }
                                },
                            ]),
                        Forms\Components\Textarea::make('permission_desc')
                            ->label('Alasan/Deskripsi')
                            ->required()
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Jelaskan alasan izin/cuti...'),
                        Forms\Components\FileUpload::make('scan_doc')
                            ->label('Dokumen Pendukung')
                            ->directory('permissions')
                            ->acceptedFileTypes(['application/pdf'])
                            ->helperText('Format: PDF. Maksimal 5MB.')
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
                            ->label(fn(Forms\Get $get) => $get('approval_status') === 'rejected' ? 'Ditolak Oleh' : 'Disetujui Oleh')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih penyetuju...')
                            ->visible(fn(Forms\Get $get) => $get('approval_status') !== 'pending'),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label(fn(Forms\Get $get) => $get('approval_status') === 'rejected' ? 'Tanggal Ditolak' : 'Tanggal Persetujuan')
                            ->native(false)
                            ->visible(fn(Forms\Get $get) => $get('approval_status') !== 'pending'),
                        Forms\Components\Textarea::make('approval_notes')
                            ->label(fn(Forms\Get $get) => $get('approval_status') === 'rejected' ? 'Alasan Penolakan' : 'Catatan Persetujuan')
                            ->rows(2)
                            ->placeholder('Catatan (jika ada)...')
                            ->visible(fn(Forms\Get $get) => $get('approval_status') !== 'pending'),
                        Forms\Components\Hidden::make('users_id')
                            ->default(Auth::id()),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi & Status Pengajuan')
                    ->description('Detail Data Pegawai, Izin/Cuti, dan Status Terkini.')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('employee.name')
                            ->label('Nama Pegawai')
                            ->weight('bold')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->color('primary')
                            ->icon('heroicon-o-user'),
                        Infolists\Components\TextEntry::make('approval_status')
                            ->label('Status Pengajuan')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'pending' => 'warning',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => $state,
                            })
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        Infolists\Components\TextEntry::make('permission.name')
                            ->label('Jenis Izin/Cuti')
                            ->weight('bold')
                            ->icon('heroicon-o-bookmark'),
                        Infolists\Components\TextEntry::make('start_permission_date')
                            ->label('Tanggal Mulai')
                            ->date('d F Y')
                            ->icon('heroicon-o-calendar-days'),
                        Infolists\Components\TextEntry::make('end_permission_date')
                            ->label('Tanggal Selesai')
                            ->date('d F Y')
                            ->icon('heroicon-o-calendar-days'),
                        Infolists\Components\TextEntry::make('permission_desc')
                            ->label('Alasan / Deskripsi')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Kronologi Proses')
                    ->description('Jejak waktu pengajuan hingga penetapan keputusan.')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Infolists\Components\ViewEntry::make('status')
                            ->hiddenLabel()
                            ->view('infolists.components.approval-timeline')
                            ->columnSpanFull()
                    ])
                    ->columns(1),
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
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Lama (Hari)')
                    ->getStateUsing(fn ($record) => \Carbon\Carbon::parse($record->start_permission_date)->diffInDays($record->end_permission_date) + 1),
                Tables\Columns\TextColumn::make('employee.remaining_leave_balance')
                    ->label('Sisa Cuti')
                    ->suffix(' Hari')
                    ->color('info'),
                Tables\Columns\TextColumn::make('accumulated_days')
                    ->label('Akumulasi')
                    ->getStateUsing(function ($record) {
                        $currentYear = \Carbon\Carbon::parse($record->start_permission_date)->year;
                        return EmployeePermission::where('employee_id', $record->employee_id)
                            ->where('approval_status', 'approved')
                            ->whereYear('start_permission_date', $currentYear)
                            ->where('start_permission_date', '<=', $record->start_permission_date)
                            ->get()
                            ->sum(fn($p) => \Carbon\Carbon::parse($p->start_permission_date)->diffInDays($p->end_permission_date) + 1) . ' Hari';
                    })
                    ->color('warning'),
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
                    ->label('Diproses Oleh')
                    ->sortable()
                    ->placeholder('Belum ada'),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Tgl Keputusan')
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
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit'),
                    Tables\Actions\Action::make('downloadForm')
                        ->label('Download Form')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(fn(EmployeePermission $record, \App\Services\LeaveFormPdfService $service) => 
                            response()->streamDownload(
                                fn() => print($service->generateLeaveForm($record)->output()),
                                "Form_Cuti_{$record->employee->name}.pdf"
                            )
                        ),
                    Tables\Actions\Action::make('approve')
                        ->label('Setujui & Upload')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn(EmployeePermission $record) => $record->approval_status === 'pending')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\FileUpload::make('scan_doc')
                                ->label('Upload Dokumen TTD Basah')
                                ->directory('permissions')
                                ->acceptedFileTypes(['application/pdf', 'image/*'])
                                ->helperText('Wajib unggah form cuti yang sudah ditandatangani fisik.')
                                ->required(),
                            Forms\Components\Textarea::make('approval_notes')
                                ->label('Catatan Persetujuan')
                                ->rows(3)
                                ->placeholder('Catatan (opsional)...'),
                        ])
                        ->action(function (EmployeePermission $record, array $data) {
                            $record->update([
                                'scan_doc' => $data['scan_doc'] ?? null,
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
                            Forms\Components\FileUpload::make('scan_doc')
                                ->label('Upload Dokumen TTD (Opsional)')
                                ->directory('permissions')
                                ->acceptedFileTypes(['application/pdf', 'image/*']),
                            Forms\Components\Textarea::make('approval_notes')
                                ->label('Alasan Penolakan')
                                ->required()
                                ->rows(3)
                                ->placeholder('Jelaskan alasan penolakan...'),
                        ])
                        ->action(function (EmployeePermission $record, array $data) {
                            $record->update([
                                'scan_doc' => $data['scan_doc'] ?? null,
                                'approval_status' => 'rejected',
                                'approved_by' => \Illuminate\Support\Facades\Auth::id(),
                                'approved_at' => now(),
                                'approval_notes' => $data['approval_notes'],
                            ]);
                        })
                        ->successNotificationTitle('Berhasil ditolak'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
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
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ])->label('Hapus yang Dipilih'),
            ])
            ->emptyStateHeading('Belum Ada Data Izin/Cuti')
            ->emptyStateDescription('Mulai dengan menambahkan data izin atau cuti Pegawai pertama.')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
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
            'index' => Pages\ListEmployeePermissions::route('/'),
            'create' => Pages\CreateEmployeePermission::route('/create'),
            'edit' => Pages\EditEmployeePermission::route('/{record}/edit'),
        ];
    }
}
