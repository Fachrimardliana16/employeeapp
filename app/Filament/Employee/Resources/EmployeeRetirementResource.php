<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeRetirementResource\Pages;
use App\Filament\Employee\Resources\EmployeeRetirementResource\RelationManagers;
use App\Models\EmployeeRetirement;
use App\Models\Employee;
use App\Models\MasterEmployeeStatusEmployment;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;

class EmployeeRetirementResource extends Resource
{
    protected static ?string $model = EmployeeRetirement::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Operasional Pegawai';

    protected static ?string $navigationLabel = 'Pensiun Pegawai';

    protected static ?string $modelLabel = 'Pengajuan Pensiun';

    protected static ?string $pluralModelLabel = 'Pengajuan Pensiun';

    protected static ?int $navigationSort = 307;

    public static function getModelLabel(): string
    {
        return 'Pengajuan Pensiun';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pengajuan Pensiun';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengajuan Pensiun')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        Forms\Components\DatePicker::make('retirement_date')
                            ->label('Tanggal Efektif')
                            ->required()
                            ->default(now())
                            ->placeholder('Pilih tanggal pensiun...')
                            ->helperText('Tanggal resmi pegawai berhenti bekerja. Minimal hari ini.')
                            ->minDate(now()->startOfDay())
                            ->native(false),

                        Forms\Components\Toggle::make('is_applied')
                            ->label('Terapkan langsung (Realisasi)')
                            ->default(false)
                            ->helperText('Jika dicentang, status di profil pegawai akan langsung diperbarui saat disimpan.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Data Pegawai')
                    ->icon('heroicon-m-user')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->placeholder('Pilih Pegawai...')
                            ->helperText('Pilih pegawai. Data riwayat akan ditampilkan otomatis.'),

                        Forms\Components\Placeholder::make('employee_info')
                            ->label('Informasi Saat Ini')
                            ->content(function (Forms\Get $get) {
                                $id = $get('employee_id');
                                if (!$id) return 'Pilih pegawai terlebih dahulu.';
                                $employee = Employee::with(['department', 'position', 'grade'])->find($id);
                                if (!$employee) return 'Data tidak ditemukan.';

                                return new \Illuminate\Support\HtmlString('
                                    <div class="text-sm space-y-1">
                                        <div><strong>NIPPAM:</strong> ' . ($employee->nippam ?? '-') . '</div>
                                        <div><strong>Bagian:</strong> ' . ($employee->department?->name ?? '-') . '</div>
                                        <div><strong>Jabatan:</strong> ' . ($employee->position?->name ?? '-') . '</div>
                                        <div><strong>Golongan:</strong> ' . ($employee->grade?->name ?? '-') . '</div>
                                    </div>
                                ');
                            })
                            ->visible(fn (Forms\Get $get) => $get('employee_id')),
                    ])->columns(2),

                Forms\Components\Section::make('Detail & Alasan')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Pensiun')
                            ->rows(3)
                            ->placeholder('Jelaskan alasan pengajuan pensiun secara rinci...')
                            ->helperText('Berikan penjelasan lengkap mengenai alasan pensiun Pegawai')
                            ->required()
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('handover_notes')
                            ->label('Catatan Serah Terima')
                            ->placeholder('Daftar pekerjaan atau tanggung jawab yang diserahterimakan...')
                            ->rows(2),

                        Forms\Components\Textarea::make('company_assets')
                            ->label('Aset Perusahaan')
                            ->placeholder('Daftar aset yang harus atau sudah dikembalikan...')
                            ->rows(2),
                    ])->columns(2),

                Forms\Components\Section::make('Dokumen Pendukung')
                    ->icon('heroicon-m-paper-clip')
                    ->schema([
                        Forms\Components\FileUpload::make('docs')
                            ->label('Berkas Usulan (PDF)')
                            ->directory('retirement-documents/proposals')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->placeholder('Unggah dokumen usulan...'),

                        Forms\Components\FileUpload::make('realization_docs')
                            ->label('Berkas Realisasi / SK (PDF)')
                            ->directory('retirement-documents/realization')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->required(fn (Forms\Get $get) => $get('is_applied'))
                            ->placeholder('Unggah dokumen SK realisasi...'),
                    ])->columns(2),

                Forms\Components\Hidden::make('users_id')
                    ->default(fn () => auth()->id()),
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
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Usulan')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

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

                Tables\Columns\TextColumn::make('is_applied')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record): string => match (true) {
                        $record->is_applied => 'success',
                        $record->approval_status === 'rejected' => 'danger',
                        $record->approval_status === 'pending' => 'warning',
                        $record->approval_status === 'approved' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($record): string => match (true) {
                        $record->is_applied => 'Realisasi',
                        $record->approval_status === 'approved' => 'Disetujui',
                        $record->approval_status === 'pending' => 'Usulan',
                        $record->approval_status === 'rejected' => 'Ditolak',
                        default => $record->approval_status,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->label('Bagian')
                    ->relationship('employee.department', 'name')
                    ->placeholder('Semua Bagian'),

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
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('terapkan_realisasi')
                    ->label('Terapkan Realisasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->size('sm')
                    ->requiresConfirmation()
                    ->modalHeading('Realisasi Pensiun Pegawai')
                    ->modalDescription('Apakah Anda yakin ingin merealisasikan pensiun untuk pegawai ini? Status pegawai akan diubah menjadi Pensiun, BPJS akan dinonaktifkan, dan akun user akan dinonaktifkan.')
                    ->form([
                        Forms\Components\DatePicker::make('retirement_date')
                            ->label('Tanggal Efektif Pensiun')
                            ->default(fn ($record) => $record->retirement_date)
                            ->required(),
                        Forms\Components\FileUpload::make('realization_docs')
                            ->label('Dokumen Realisasi (SK Pensiun, dll)')
                            ->directory('retirement-docs/realization')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $employee = $record->employee;
                        if (!$employee) return;

                        // 1. Dapatkan ID status Pensiun
                        $pensiunStatus = MasterEmployeeStatusEmployment::where('name', 'Pensiun')->first();
                        
                        // 2. Update Profil Pegawai
                        $employee->update([
                            'employment_status_id' => $pensiunStatus?->id ?? $employee->employment_status_id,
                            'bpjs_kes_status' => 'Non-Aktif',
                            'bpjs_tk_status' => 'Non-Aktif',
                            'dapenma_status' => 'Non-Aktif',
                            'agreement_date_end' => $data['retirement_date'],
                        ]);

                        // 3. Deaktivasi User Account
                        if ($employee->users_id) {
                            User::where('id', $employee->users_id)->update(['is_active' => false]);
                        }

                        // 4. Update data Pensiun
                        $record->update([
                            'is_applied' => true,
                            'applied_at' => now(),
                            'applied_by' => auth()->id(),
                            'retirement_date' => $data['retirement_date'],
                            'realization_docs' => $data['realization_docs'],
                            'approval_status' => 'approved',
                        ]);

                        Notification::make()
                            ->title('Pensiun Berhasil Direalisasikan')
                            ->body('Status pegawai ' . $employee->name . ' telah diperbarui dan akun telah dinonaktifkan.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => !$record->is_applied),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat')
                        ->modalHeading('Detail Pensiun Pegawai')
                        ->modalWidth('4xl'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->modalHeading('Ubah Data Pensiun')
                        ->modalWidth('4xl'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Data Pensiun')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data pensiun ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
                ])
                    ->label('Lainnya')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih')
                        ->modalHeading('Hapus Data Pensiun yang Dipilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua data pensiun yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->modalCancelActionLabel('Batal'),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ])->label('Hapus yang Dipilih'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('cetak_laporan')
                    ->label('Cetak Laporan')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->default(now()->startOfMonth()),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->default(now()),
                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai (Opsional)')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data) {
                        return redirect()->route('report.career-movement', [
                            'type' => 'retirement',
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date'],
                            'employee_id' => $data['employee_id'],
                        ]);
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum Ada Data Pensiun')
            ->emptyStateDescription('Mulai dengan menambahkan data pensiun Pegawai pertama.')
            ->emptyStateIcon('heroicon-o-home');
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
            'index' => Pages\ListEmployeeRetirements::route('/'),
            'create' => Pages\CreateEmployeeRetirement::route('/create'),
            'edit' => Pages\EditEmployeeRetirement::route('/{record}/edit'),
        ];
    }
}
