<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\MyPermissionResource\Pages;
use App\Models\EmployeePermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyPermissionResource extends Resource
{
    protected static ?string $model = EmployeePermission::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Data Saya';

    protected static ?string $navigationLabel = 'Izin & Cuti';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $employee = \App\Models\Employee::where('email', $user->email)->first();

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
                            ->preload(),

                        Forms\Components\DatePicker::make('start_permission_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->native(false),

                        Forms\Components\DatePicker::make('end_permission_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->native(false)
                            ->afterOrEqual('start_permission_date'),

                        Forms\Components\Textarea::make('permission_desc')
                            ->label('Alasan/Deskripsi')
                            ->required()
                            ->rows(3)
                            ->placeholder('Jelaskan alasan pengajuan izin/cuti...')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('scan_doc')
                            ->label('Dokumen Pendukung')
                            ->directory('permissions')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->helperText('Upload surat keterangan dokter, undangan, dll (opsional)')
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
                            ->label('Disetujui Oleh')
                            ->content(fn($record) => $record?->approver?->name ?? 'Belum ada'),

                        Forms\Components\Placeholder::make('approved_at_view')
                            ->label('Tanggal Persetujuan')
                            ->content(fn($record) => $record?->approved_at?->format('d/m/Y H:i') ?? 'Belum ada'),

                        Forms\Components\Placeholder::make('approval_notes_view')
                            ->label('Catatan')
                            ->content(fn($record) => $record?->approval_notes ?? 'Tidak ada catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->hidden(fn($operation) => $operation === 'create'),
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
                    ->label('Disetujui Oleh')
                    ->placeholder('Belum ada'),

                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Tgl Persetujuan')
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->approval_status === 'pending'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->approval_status === 'pending'),
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
