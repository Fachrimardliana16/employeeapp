<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\EmployeeDocumentResource\Pages;
use App\Filament\User\Resources\EmployeeDocumentResource\RelationManagers;
use App\Models\EmployeeDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class EmployeeDocumentResource extends Resource
{
    protected static ?string $model = EmployeeDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Data Mandiri';

    protected static ?string $navigationLabel = 'Dokumen Pegawai';

    protected static ?string $modelLabel = 'Dokumen Pegawai';

    protected static ?string $pluralModelLabel = 'Dokumen Pegawai';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dokumen')
                    ->schema([
                        Forms\Components\Select::make('master_employee_archive_type_id')
                            ->label('Jenis Dokumen')
                            ->relationship('archiveType', 'name', fn($query) => $query->where('is_active', true))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('document_name')
                            ->label('Nama Dokumen')
                            ->placeholder('Contoh: KTP - John Doe')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('document_number')
                            ->label('Nomor Dokumen')
                            ->placeholder('Nomor ID/Seri dokumen (opsional)')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->placeholder('Catatan tambahan tentang dokumen ini')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tanggal & Validitas')
                    ->schema([
                        Forms\Components\DatePicker::make('issue_date')
                            ->label('Tanggal Terbit')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('expiry_date')
                            ->label('Tanggal Kadaluarsa')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->after('issue_date')
                            ->helperText('Kosongkan jika dokumen tidak memiliki masa berlaku'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Upload Dokumen')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('File Dokumen')
                            ->disk('public')
                            ->visibility('public')
                            ->directory('employee-documents')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->imageEditor()
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('1024')
                            ->maxSize(10240)
                            ->downloadable()
                            ->previewable()
                            ->required()
                            ->helperText('Format: PDF, JPG, PNG. Maksimal 10MB')
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('employee_id')
                            ->default(fn() => Auth::user()->employee?->id),
                        Forms\Components\Hidden::make('users_id')
                            ->default(fn() => auth()->id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('employee_id', Auth::user()->employee->id ?? 0))
            ->columns([
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Jenis Dokumen')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('document_name')
                    ->label('Nama Dokumen')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('document_number')
                    ->label('Nomor')
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Tanggal Terbit')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Kadaluarsa')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Tidak ada')
                    ->badge(),
                Tables\Columns\IconColumn::make('file_path')
                    ->label('File')
                    ->icon('heroicon-o-document')
                    ->url(fn($record) => $record->file_path ? url('image-view/' . $record->file_path) : null)
                    ->openUrlInNewTab()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diupload')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->label('Jenis Dokumen')
                    ->options([
                        'KTP' => 'KTP',
                        'KK' => 'Kartu Keluarga',
                        'NPWP' => 'NPWP',
                        'BPJS Kesehatan' => 'BPJS Kesehatan',
                        'BPJS Ketenagakerjaan' => 'BPJS Ketenagakerjaan',
                        'Ijazah' => 'Ijazah',
                        'Transkrip Nilai' => 'Transkrip Nilai',
                        'Sertifikat' => 'Sertifikat',
                        'SIM' => 'SIM',
                        'Akta Kelahiran' => 'Akta Kelahiran',
                        'Surat Keterangan Sehat' => 'Surat Keterangan Sehat',
                        'SKCK' => 'SKCK',
                        'Lainnya' => 'Lainnya',
                    ]),
                Tables\Filters\Filter::make('kadaluarsa')
                    ->label('Sudah Kadaluarsa')
                    ->query(fn(Builder $query) => $query->whereNotNull('expiry_date')->where('expiry_date', '<', now())),
                Tables\Filters\Filter::make('akan_kadaluarsa')
                    ->label('Akan Kadaluarsa (< 30 hari)')
                    ->query(fn(Builder $query) => $query->whereNotNull('expiry_date')
                        ->whereBetween('expiry_date', [now(), now()->addDays(30)])),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),
                    Tables\Actions\EditAction::make()->label('Edit'),
                    Tables\Actions\Action::make('download')
                        ->label('Download')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn($record) => $record->file_path ? url('image-view/' . $record->file_path) : null)
                        ->openUrlInNewTab(),
                    Tables\Actions\DeleteAction::make()->label('Hapus'),
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
                ])->label('Hapus yang Dipilih'),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListEmployeeDocuments::route('/'),
            'create' => Pages\CreateEmployeeDocument::route('/create'),
            'view' => Pages\ViewEmployeeDocument::route('/{record}'),
            'edit' => Pages\EditEmployeeDocument::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $employeeId = Auth::user()->employee->id ?? 0;
        $expiringSoon = static::getModel()::where('employee_id', $employeeId)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(30)])
            ->count();

        return $expiringSoon > 0 ? (string) $expiringSoon : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
