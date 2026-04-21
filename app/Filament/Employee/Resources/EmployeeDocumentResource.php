<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeDocumentResource\Pages;
use App\Filament\Employee\Resources\EmployeeDocumentResource\RelationManagers;
use App\Models\EmployeeDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;

class EmployeeDocumentResource extends Resource
{
    protected static ?string $model = EmployeeDocument::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Manajemen Pegawai';
    protected static ?string $navigationLabel = 'Dokumen Pegawai';
    protected static ?string $modelLabel = 'Dokumen Pegawai';
    protected static ?string $pluralModelLabel = 'Dokumen Pegawai';
    protected static ?int $navigationSort = 203;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dokumen')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('document_type')
                            ->label('Jenis Dokumen')
                            ->options([
                                'KTP' => 'KTP',
                                'KK' => 'Kartu Keluarga',
                                'NPWP' => 'NPWP',
                                'BPJS_Kesehatan' => 'BPJS Kesehatan',
                                'BPJS_Ketenagakerjaan' => 'BPJS Ketenagakerjaan',
                                'Ijazah' => 'Ijazah',
                                'Transkrip' => 'Transkrip Nilai',
                                'Sertifikat' => 'Sertifikat',
                                'Kontrak_Kerja' => 'Kontrak Kerja',
                                'SK' => 'Surat Keputusan',
                                'SKCK' => 'SKCK',
                                'Surat_Sehat' => 'Surat Keterangan Sehat',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->required()
                            ->searchable()
                            ->native(false),

                        Forms\Components\TextInput::make('document_number')
                            ->label('Nomor Dokumen')
                            ->maxLength(255)
                            ->placeholder('Contoh: 3201xxxxxxxxxx'),

                        Forms\Components\TextInput::make('document_name')
                            ->label('Nama Dokumen')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: KTP - John Doe'),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload Dokumen')
                            ->disk('public')
                            ->visibility('public')
                            ->directory('employee-documents')
                            ->acceptedFileTypes(fn (Forms\Get $get): array => $get('document_type') === 'SK' ? ['application/pdf'] : ['application/pdf', 'image/jpeg', 'image/png'])
                            ->imageEditor()
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('1024')
                            ->maxSize(10240)
                            ->required()
                            ->helperText(fn (Forms\Get $get): string => $get('document_type') === 'SK' ? 'Upload file PDF (max 10MB)' : 'Upload file PDF atau Gambar (JPG/PNG) maksimal 10MB')
                            ->downloadable()
                            ->openable()
                            ->previewable()
                            ->columnSpanFull(),
                    ])->columns(2),

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
                            ->afterOrEqual('issue_date')
                            ->helperText('Kosongkan jika tidak ada masa berlaku'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->placeholder('Catatan tambahan mengenai dokumen ini')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Hidden::make('uploaded_by')
                    ->default('hr'),
                Forms\Components\Hidden::make('users_id')
                    ->default(fn() => \Illuminate\Support\Facades\Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('document_type')
                    ->label('Jenis Dokumen')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'KTP', 'KK', 'NPWP' => 'success',
                        'BPJS_Kesehatan', 'BPJS_Ketenagakerjaan' => 'info',
                        'Ijazah', 'Transkrip', 'Sertifikat' => 'warning',
                        'Kontrak_Kerja', 'SK' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('document_name')
                    ->label('Nama Dokumen')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('document_number')
                    ->label('Nomor Dokumen')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Tgl Terbit')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Tgl Kadaluarsa')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('uploaded_by')
                    ->label('Diupload Oleh')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'hr' => 'info',
                        'employee' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'hr' => 'HR',
                        'employee' => 'Pegawai',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diupload Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->label('Jenis Dokumen')
                    ->options([
                        'KTP' => 'KTP',
                        'KK' => 'Kartu Keluarga',
                        'NPWP' => 'NPWP',
                        'BPJS_Kesehatan' => 'BPJS Kesehatan',
                        'BPJS_Ketenagakerjaan' => 'BPJS Ketenagakerjaan',
                        'Ijazah' => 'Ijazah',
                        'Sertifikat' => 'Sertifikat',
                        'Kontrak_Kerja' => 'Kontrak Kerja',
                    ]),

                Tables\Filters\SelectFilter::make('uploaded_by')
                    ->label('Diupload Oleh')
                    ->options([
                        'hr' => 'HR',
                        'employee' => 'Pegawai',
                    ]),

                Tables\Filters\Filter::make('expired')
                    ->label('Kadaluarsa')
                    ->query(fn(Builder $query): Builder => $query->where('expiry_date', '<', now()))
                    ->toggle(),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Akan Kadaluarsa (< 30 hari)')
                    ->query(
                        fn(Builder $query): Builder => $query
                            ->where('expiry_date', '>=', now())
                            ->where('expiry_date', '<=', now()->addDays(30))
                    )
                    ->toggle(),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),
                    Tables\Actions\EditAction::make()->label('Edit'),
                    Tables\Actions\DeleteAction::make()->label('Hapus'),
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
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus yang Dipilih'),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['employee']);
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
            'edit' => Pages\EditEmployeeDocument::route('/{record}/edit'),
        ];
    }
}
