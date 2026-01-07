<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeBusinessTravelLetterResource\Pages;
use App\Filament\Employee\Resources\EmployeeBusinessTravelLetterResource\RelationManagers;
use App\Models\EmployeeBusinessTravelLetter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeBusinessTravelLetterResource extends Resource
{
    protected static ?string $model = EmployeeBusinessTravelLetter::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Surat & Tugas Dinas';

    protected static ?string $navigationLabel = 'Perjalanan Dinas';

    protected static ?string $modelLabel = 'Surat Perjalanan Dinas';

    protected static ?string $pluralModelLabel = 'Surat Perjalanan Dinas';

    protected static ?int $navigationSort = 702;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar Perjalanan Dinas')
                    ->schema([
                        Forms\Components\TextInput::make('registration_number')
                            ->label('Nomor Surat Perjalanan Dinas')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: SPD/001/2024'),

                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai Utama')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih pegawai utama (opsional)')
                            ->helperText('Pegawai utama yang bertanggung jawab. Kosongkan jika tidak ada.'),

                        Forms\Components\Select::make('additional_employee_ids')
                            ->label('Pegawai Tambahan')
                            ->multiple()
                            ->options(function () {
                                return \App\Models\Employee::pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih satu atau lebih pegawai tambahan (opsional)')
                            ->helperText('Anda bisa memilih satu pegawai atau beberapa pegawai sekaligus yang ikut dalam perjalanan dinas')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('purpose_of_trip')
                            ->label('Tujuan Perjalanan Dinas')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Destinasi dan Periode')
                    ->schema([
                        Forms\Components\TextInput::make('destination')
                            ->label('Tujuan/Destinasi')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('destination_detail')
                            ->label('Detail Destinasi')
                            ->rows(2)
                            ->placeholder('Detail lokasi atau alamat lengkap'),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Berangkat')
                            ->required(),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Kembali')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Anggaran dan Biaya')
                    ->schema([
                        Forms\Components\TextInput::make('business_trip_expenses')
                            ->label('Biaya Perjalanan Dinas')
                            ->numeric()
                            ->prefix('Rp')
                            ->step(0.01)
                            ->default(0.00),

                        Forms\Components\TextInput::make('pasal')
                            ->label('Pasal/Dasar Hukum')
                            ->maxLength(255)
                            ->placeholder('Contoh: Pasal 10 ayat 2'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Penandatangan dan Keterangan')
                    ->schema([
                        Forms\Components\Select::make('signatory_employee_id')
                            ->label('Pilih Penandatangan')
                            ->options(function () {
                                return \App\Models\Employee::getSignatoryEmployees();
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih dari pegawai yang berjabatan Direktur/Kepala Bagian')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $employee = \App\Models\Employee::getSignatoryById($state);
                                    if ($employee) {
                                        $set('signatory_name', $employee->name);
                                        $set('signatory_position', $employee->position->name ?? '');
                                    }
                                } else {
                                    // Jika dropdown dikosongkan, bersihkan field
                                    $set('signatory_name', '');
                                    $set('signatory_position', '');
                                }
                            }),

                        Forms\Components\TextInput::make('signatory_name')
                            ->label('Nama Penandatangan')
                            ->placeholder('Akan terisi otomatis setelah memilih pegawai, atau isi manual jika tidak memilih dari dropdown')
                            ->maxLength(255)
                            ->required()
                            ->disabled(fn (callable $get) => !empty($get('signatory_employee_id')))
                            ->dehydrated(true)
                            ->helperText(fn (callable $get) => !empty($get('signatory_employee_id')) 
                                ? 'Field ini terkunci karena Anda sudah memilih penandatangan dari dropdown. Kosongkan dropdown untuk mengedit manual.' 
                                : 'Isi manual jika tidak memilih dari dropdown di atas.'),

                        Forms\Components\TextInput::make('signatory_position')
                            ->label('Jabatan Penandatangan')
                            ->placeholder('Akan terisi otomatis setelah memilih pegawai, atau isi manual jika tidak memilih dari dropdown')
                            ->maxLength(255)
                            ->required()
                            ->disabled(fn (callable $get) => !empty($get('signatory_employee_id')))
                            ->dehydrated(true)
                            ->helperText(fn (callable $get) => !empty($get('signatory_employee_id')) 
                                ? 'Field ini terkunci karena Anda sudah memilih penandatangan dari dropdown. Kosongkan dropdown untuk mengedit manual.' 
                                : 'Isi manual jika tidak memilih dari dropdown di atas.'),

                        Forms\Components\Select::make('employee_signatory_id')
                            ->label('Penandatangan (Legacy)')
                            ->relationship('signatory', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Field lama - gunakan "Pilih Penandatangan" di atas')
                            ->placeholder('Field legacy - tidak perlu diisi')
                            ->columnSpanFull()
                            ->hidden(),

                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan Tambahan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')
                    ->label('Nomor Surat')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Pegawai Utama')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Tidak ada pegawai utama')
                    ->default('—'),

                Tables\Columns\TextColumn::make('additional_employees_names')
                    ->label('Pegawai Tambahan')
                    ->limit(40)
                    ->placeholder('—')
                    ->getStateUsing(function ($record) {
                        if (empty($record->additional_employee_ids)) {
                            return '—';
                        }
                        $employees = $record->additionalEmployees();
                        $count = $employees->count();
                        $names = $employees->pluck('name')->join(', ');

                        if ($count > 0) {
                            return "({$count}) {$names}";
                        }
                        return '—';
                    })
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (empty($state) || $state === '—' || strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_employees')
                    ->label('Total Pegawai')
                    ->getStateUsing(function ($record) {
                        $total = 0;
                        if ($record->employee_id) {
                            $total += 1; // Pegawai utama
                        }
                        if (!empty($record->additional_employee_ids)) {
                            $total += count($record->additional_employee_ids); // Pegawai tambahan
                        }
                        return $total > 0 ? $total . ' orang' : 'Tidak ada';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state === 'Tidak ada' => 'gray',
                        str_contains($state, '1 orang') => 'warning',
                        default => 'success',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('purpose_of_trip')
                    ->label('Tujuan Perjalanan')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('destination')
                    ->label('Destinasi')
                    ->searchable()
                    ->limit(25),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Berangkat')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Kembali')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('business_trip_expenses')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('signatory.name')
                    ->label('Penandatangan')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pasal')
                    ->label('Pasal')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pdf_status')
                    ->label('Status PDF')
                    ->getStateUsing(function ($record) {
                        if (empty($record->pdf_file_path)) {
                            return 'Belum dibuat';
                        }
                        
                        $fullPath = storage_path('app/public/' . $record->pdf_file_path);
                        if (file_exists($fullPath)) {
                            return 'Tersedia';
                        }
                        
                        return 'File hilang';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Tersedia' => 'success',
                        'Belum dibuat' => 'warning',
                        'File hilang' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('travel_date')
                    ->label('Periode Perjalanan')
                    ->form([
                        Forms\Components\DatePicker::make('travel_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('travel_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['travel_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['travel_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    }),

                Tables\Filters\SelectFilter::make('employee_signatory_id')
                    ->label('Penandatangan')
                    ->relationship('signatory', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\Action::make('view_pdf')
                    ->label('Lihat PDF')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (EmployeeBusinessTravelLetter $record): bool => !empty($record->pdf_file_path) && file_exists(storage_path('app/public/' . $record->pdf_file_path)))
                    ->url(fn (EmployeeBusinessTravelLetter $record): string => asset('storage/' . $record->pdf_file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('generate_pdf')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (EmployeeBusinessTravelLetter $record) {
                        // Prioritas: data dari relasi pegawai > data manual > legacy > default
                        $signatoryName = $record->signatory_name;
                        $signatoryPosition = $record->signatory_position;
                        
                        if ($record->signatoryEmployee) {
                            $signatoryName = $record->signatoryEmployee->name;
                            $signatoryPosition = $record->signatoryEmployee->position->name ?? $signatoryPosition;
                        } elseif ($record->signatory) {
                            // Fallback ke legacy field
                            $signatoryName = $record->signatory->name;
                            $signatoryPosition = $record->signatory->position->name ?? $signatoryPosition;
                        }
                        
                        // Fallback ke default jika kosong
                        $signatoryName = $signatoryName ?: 'Direktur PERUMDA';
                        $signatoryPosition = $signatoryPosition ?: 'Direktur';
                        
                        // Generate PDF dengan data dinamis
                        $pdf = Pdf::loadView('pdf.business-travel-letter', [
                            'travel' => $record,
                            'signatory_name' => $signatoryName,
                            'signatory_position' => $signatoryPosition
                        ]);
                        
                        $filename = 'Surat_Perjalanan_Dinas_' . $record->registration_number . '_' . date('Y-m-d') . '.pdf';
                        $filename = str_replace(['/', '\\', ' '], '_', $filename);
                        
                        // Simpan PDF ke storage
                        $pdfPath = 'business_travel_letters/' . $filename;
                        $fullPath = storage_path('app/public/' . $pdfPath);
                        
                        // Buat direktori jika belum ada
                        if (!file_exists(dirname($fullPath))) {
                            mkdir(dirname($fullPath), 0755, true);
                        }
                        
                        // Simpan file PDF
                        $pdf->save($fullPath);
                        
                        // Update record dengan path PDF
                        $record->update(['pdf_file_path' => $pdfPath]);
                        
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, $filename);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus'),
                ]),
            ])
            ->emptyStateHeading('Belum ada surat perjalanan dinas')
            ->emptyStateDescription('Mulai dengan membuat surat perjalanan dinas pertama Anda.')
            ->emptyStateIcon('heroicon-o-globe-alt');
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
            'index' => Pages\ListEmployeeBusinessTravelLetters::route('/'),
            'create' => Pages\CreateEmployeeBusinessTravelLetter::route('/create'),
            'edit' => Pages\EditEmployeeBusinessTravelLetter::route('/{record}/edit'),
        ];
    }
}
