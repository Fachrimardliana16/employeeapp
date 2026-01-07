<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\EmployeeRetirementResource\Pages;
use App\Models\EmployeeRetirement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EmployeeRetirementResource extends Resource
{
    protected static ?string $model = EmployeeRetirement::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-on-rectangle';
    protected static ?string $navigationGroup = 'Data Saya';
    protected static ?string $navigationLabel = 'Pensiun/Resign';
    protected static ?int $navigationSort = 4;

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

        return $form->schema([
            Forms\Components\Section::make('Data Pengunduran Diri')
                ->description('Isi informasi pengunduran diri/pensiun Anda')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->schema([
                    Forms\Components\Select::make('retirement_type')
                        ->label('Jenis Pengunduran Diri')
                        ->options([
                            'resign' => 'Resign (Mengundurkan Diri)',
                            'pension' => 'Pensiun',
                            'contract_end' => 'Akhir Kontrak',
                            'termination' => 'Pemutusan Hubungan Kerja',
                        ])
                        ->required()
                        ->native(false)
                        ->live(),

                    Forms\Components\DatePicker::make('retirement_date')
                        ->label('Tanggal Efektif')
                        ->required()
                        ->native(false)
                        ->minDate(now()->addDays(30))
                        ->helperText('Minimal 30 hari dari hari ini (sesuai masa pemberitahuan)'),

                    Forms\Components\DatePicker::make('last_working_day')
                        ->label('Hari Kerja Terakhir')
                        ->required()
                        ->native(false)
                        ->afterOrEqual('retirement_date')
                        ->helperText('Hari terakhir Anda bekerja'),

                    Forms\Components\Textarea::make('reason')
                        ->label('Alasan Pengunduran Diri')
                        ->required()
                        ->rows(4)
                        ->placeholder('Jelaskan alasan pengunduran diri secara profesional...')
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('docs')
                        ->label('Surat Pengunduran Diri')
                        ->directory('retirements')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(5120)
                        ->required()
                        ->helperText('Upload surat pengunduran diri resmi (PDF, max 5MB)')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Serah Terima & Clearance')
                ->description('Informasi serah terima pekerjaan dan aset perusahaan')
                ->icon('heroicon-o-clipboard-document-check')
                ->schema([
                    Forms\Components\Textarea::make('handover_notes')
                        ->label('Catatan Serah Terima Pekerjaan')
                        ->rows(3)
                        ->placeholder('Jelaskan pekerjaan yang sedang berjalan, password sistem, kontak penting, dll')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('company_assets')
                        ->label('Daftar Aset Perusahaan yang Dikembalikan')
                        ->rows(3)
                        ->placeholder('Laptop, ID Card, kunci kantor, buku, dll')
                        ->helperText('List semua aset perusahaan yang akan dikembalikan')
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('handover_document')
                        ->label('Dokumen Serah Terima')
                        ->directory('retirements/handover')
                        ->acceptedFileTypes(['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->maxSize(10240)
                        ->multiple()
                        ->helperText('Upload dokumen serah terima (PDF/Excel, max 10MB per file)')
                        ->columnSpanFull(),
                ])->columns(1)->collapsible(),

            Forms\Components\Section::make('Informasi Tambahan')
                ->description('Informasi untuk proses administrasi')
                ->icon('heroicon-o-information-circle')
                ->schema([
                    Forms\Components\TextInput::make('forwarding_address')
                        ->label('Alamat Setelah Resign')
                        ->maxLength(500)
                        ->placeholder('Alamat lengkap untuk korespondensi')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('forwarding_phone')
                        ->label('No. Telepon yang Bisa Dihubungi')
                        ->tel()
                        ->maxLength(20)
                        ->placeholder('08xxx'),

                    Forms\Components\TextInput::make('forwarding_email')
                        ->label('Email yang Bisa Dihubungi')
                        ->email()
                        ->maxLength(255)
                        ->placeholder('email@example.com'),

                    Forms\Components\Toggle::make('need_reference_letter')
                        ->label('Butuh Surat Referensi?')
                        ->default(false)
                        ->inline(false)
                        ->helperText('Centang jika memerlukan surat referensi kerja'),

                    Forms\Components\Toggle::make('agree_exit_interview')
                        ->label('Bersedia Exit Interview?')
                        ->default(true)
                        ->inline(false)
                        ->helperText('Exit interview membantu perusahaan meningkatkan kualitas kerja'),

                    Forms\Components\Textarea::make('feedback')
                        ->label('Feedback & Saran untuk Perusahaan')
                        ->rows(4)
                        ->placeholder('Masukan Anda sangat berharga untuk perbaikan perusahaan (opsional)')
                        ->columnSpanFull(),
                ])->columns(2)->collapsible(),
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
                ])->columns(3)->hidden(fn($operation) => $operation === 'create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('retirement_type')
                ->label('Jenis')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'resign' => 'info',
                    'pension' => 'success',
                    'contract_end' => 'warning',
                    'termination' => 'danger',
                    default => 'gray',
                })
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    'resign' => 'Resign',
                    'pension' => 'Pensiun',
                    'contract_end' => 'Akhir Kontrak',
                    'termination' => 'PHK',
                    default => $state,
                }),

            Tables\Columns\TextColumn::make('retirement_date')
                ->label('Tanggal Efektif')
                ->date('d/m/Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('last_working_day')
                ->label('Hari Terakhir')
                ->date('d/m/Y')
                ->sortable()
                ->toggleable(),

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
                })
                ->sortable(),

            Tables\Columns\IconColumn::make('need_reference_letter')
                ->label('Surat Ref')
                ->boolean()
                ->toggleable(),

            Tables\Columns\TextColumn::make('approver.name')
                ->label('Disetujui Oleh')
                ->placeholder('Belum ada')
                ->toggleable(),

            Tables\Columns\TextColumn::make('approved_at')
                ->label('Tgl Persetujuan')
                ->dateTime('d/m/Y')
                ->placeholder('Belum ada')
                ->sortable()
                ->toggleable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Diajukan')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->visible(fn($record) => $record->approval_status === 'pending'),
                Tables\Actions\DeleteAction::make()->visible(fn($record) => $record->approval_status === 'pending'),
            ]);
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
