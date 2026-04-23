<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\MyPermissionResource\Pages;
use App\Models\EmployeePermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyPermissionResource extends Resource
{
    protected static ?string $model = EmployeePermission::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Presensi & Laporan';

    protected static ?string $navigationLabel = 'Izin & Cuti';

    protected static ?string $modelLabel = 'Izin & Cuti';

    protected static ?string $pluralModelLabel = 'Izin & Cuti';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $employee = $user->employee;

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
        return $record->approval_status === 'pending';
    }

    public static function canDelete($record): bool
    {
        return $record->approval_status === 'pending';
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $employee = \App\Models\Employee::where('email', $user->email)->first();

        return $form
            ->schema([
                Forms\Components\Section::make('Pengajuan Izin/Cuti')
                    ->schema([
                        Forms\Components\Select::make('permission_id')
                            ->label('Jenis Izin/Cuti')
                            ->relationship('permission', 'name')
                            ->required()
                            ->searchable()
                            ->columnSpanFull()
                            ->preload(),

                        Forms\Components\DatePicker::make('start_permission_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->native(false)
                            ->prefixIcon('heroicon-m-calendar')
                            ->placeholder('Pilih tanggal mulai...'),

                        Forms\Components\DatePicker::make('end_permission_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->native(false)
                            ->afterOrEqual('start_permission_date')
                            ->prefixIcon('heroicon-m-calendar')
                            ->placeholder('Pilih tanggal selesai...'),

                        Forms\Components\Textarea::make('permission_desc')
                            ->label('Alasan/Deskripsi')
                            ->required()
                            ->rows(3)
                            ->placeholder('Jelaskan alasan pengajuan izin/cuti...')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('scan_doc')
                            ->label('Dokumen yang Sudah Ditandatangani')
                            ->directory('permissions')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->hidden(fn($operation) => $operation === 'create')
                            ->helperText('Form yang sudah diprint dan ditandatangani basah (PDF).')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status Persetujuan')
                    ->schema([
                        Forms\Components\Placeholder::make('approval_status_view')
                            ->label('Status')
                            ->content(fn($record) => match ($record?->approval_status ?? 'pending') {
                                'pending' => '⏳ Menunggu Persetujuan',
                                'approved' => '✅ Disetujui',
                                'rejected' => '❌ Ditolak',
                                default => 'Belum ada',
                            }),

                        Forms\Components\Placeholder::make('approver_name')
                            ->label(fn($record) => $record?->approval_status === 'rejected' ? 'Ditolak Oleh' : 'Disetujui Oleh')
                            ->content(fn($record) => $record?->approver?->name ?? 'Belum ada'),

                        Forms\Components\Placeholder::make('approved_at_view')
                            ->label(fn($record) => $record?->approval_status === 'rejected' ? 'Tanggal Ditolak' : 'Tanggal Persetujuan')
                            ->content(fn($record) => $record?->approved_at?->format('d/m/Y H:i') ?? 'Belum ada'),

                        Forms\Components\Placeholder::make('approval_notes_view')
                            ->label(fn($record) => $record?->approval_status === 'rejected' ? 'Alasan Penolakan' : 'Catatan Persetujuan')
                            ->content(fn($record) => $record?->approval_notes ?? 'Tidak ada catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->hidden(fn($operation) => $operation === 'create'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi & Status Pengajuan')
                    ->description('Detail jenis, tanggal izin/cuti beserta statusnya.')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('approval_status')
                            ->label('Status Terkini')
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
                            ->color('primary')
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
                Tables\Columns\TextColumn::make('permission.name')
                    ->label('Jenis Izin/Cuti'),

                Tables\Columns\TextColumn::make('start_permission_date')
                    ->label('Tanggal Mulai')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('end_permission_date')
                    ->label('Tanggal Selesai')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Lama (Hari)')
                    ->getStateUsing(fn ($record) => \Carbon\Carbon::parse($record->start_permission_date)->diffInDays($record->end_permission_date) + 1),

                Tables\Columns\TextColumn::make('employee.remaining_leave_balance')
                    ->label('Sisa Cuti')
                    ->suffix(' Hari')
                    ->color('info'),

                Tables\Columns\TextColumn::make('accumulated_days')
                    ->label('Akumulasi (Thn Ini)')
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
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Diproses Oleh')
                    ->placeholder('Belum ada'),

                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Tgl Keputusan')
                    ->dateTime('d/m/Y')
                    ->placeholder('Belum ada'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('approval_status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),
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
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->visible(fn($record) => $record->approval_status === 'pending'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->visible(fn($record) => $record->approval_status === 'pending'),
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
            'index' => Pages\ListMyPermissions::route('/'),
            'create' => Pages\CreateMyPermission::route('/create'),
            'edit' => Pages\EditMyPermission::route('/{record}/edit'),
            'view' => Pages\ViewMyPermission::route('/{record}'),
        ];
    }
}
