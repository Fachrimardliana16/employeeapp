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

class EmployeeDocumentResource extends Resource
{
    protected static ?string $model = EmployeeDocument::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Dokumen & Arsip';
    protected static ?string $navigationLabel = 'Dokumen Karyawan';
    protected static ?int $navigationSort = 301;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dokumen')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Karyawan')
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
                            ->directory('employee-documents')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240)
                            ->required()
                            ->helperText('Upload file PDF atau gambar (max 10MB)')
                            ->downloadable()
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

                Forms\Components\Section::make('Informasi Upload')
                    ->schema([
                        Forms\Components\Select::make('uploaded_by')
                            ->label('Diupload Oleh')
                            ->options([
                                'hr' => 'HR/Admin',
                                'employee' => 'Karyawan Sendiri',
                            ])
                            ->required()
                            ->default('hr')
                            ->native(false),

                        Forms\Components\Hidden::make('users_id')
                            ->default(fn() => \Illuminate\Support\Facades\Auth::id()),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Karyawan')
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
                        'employee' => 'Karyawan',
                        default => $state,
                    })
                    ->toggleable(),

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
                        'employee' => 'Karyawan',
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListEmployeeDocuments::route('/'),
            'create' => Pages\CreateEmployeeDocument::route('/create'),
            'edit' => Pages\EditEmployeeDocument::route('/{record}/edit'),
        ];
    }
}
