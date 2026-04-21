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
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use App\Models\MasterStandarHargaSatuan;
use App\Models\Employee;

class EmployeeBusinessTravelLetterResource extends Resource
{
    protected static ?string $model = EmployeeBusinessTravelLetter::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Surat & Tugas Dinas';

    protected static ?string $navigationLabel = 'Perjalanan Dinas';

    protected static ?string $modelLabel = 'Surat Perjalanan Dinas';

    protected static ?string $pluralModelLabel = 'Surat Perjalanan Dinas';

    protected static ?int $navigationSort = 702;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'on progress')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', 'on progress')->count() > 0 ? 'warning' : 'gray';
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        try {
            $startDate = $get('start_date');
            $endDate = $get('end_date');
            $days = 0;

            if ($startDate && $endDate) {
                $start = \Carbon\Carbon::parse($startDate);
                $end = \Carbon\Carbon::parse($endDate);
                $days = $start->diffInDays($end) + 1;
            }

            $shsCategory = $get('shs_category');
            $shsLocation = $get('shs_location');
            
            $totalEmployees = 0;
            $totalPocketMoney = 0;
            $accommodationNote = "";

            // 1. Pegawai Utama
            if ($mainEmpId = $get('employee_id')) {
                $totalEmployees += 1;
                $mainEmp = Employee::with(['position', 'grade'])->find($mainEmpId);
                
                $mainPocketMoney = (float)($get('pocket_money_cost') ?: 0);

                if ($mainEmp && $shsCategory && $shsLocation) {
                    $mappedSpesifikasi = MasterStandarHargaSatuan::mapPositionToSpesifikasi(
                        $mainEmp->position->name ?? '', 
                        $mainEmp->grade->name ?? null
                    );

                    $baseLoc = preg_match('/^ZONA\s+[IVXLCDM]+/i', $shsLocation, $m) ? strtoupper($m[0]) : $shsLocation;

                    $shs = MasterStandarHargaSatuan::where('category', $shsCategory)
                        ->where(function ($query) use ($shsLocation, $baseLoc) {
                            $query->where('location', trim($shsLocation))
                                  ->orWhere('location', 'LIKE', $baseLoc . '%');
                        })
                        ->where('spesifikasi', trim($mappedSpesifikasi))
                        ->first();

                    if ($shs) {
                        $mainPocketMoney = (float)$shs->amount;
                        $set('pocket_money_cost', $mainPocketMoney);
                        if (!empty($shs->description)) {
                            $accommodationNote = "Rekomendasi Utama: " . $shs->description;
                        }
                    }
                }
                
                $totalPocketMoney += ($mainPocketMoney * $days);
            }

            // 2. Pegawai Tambahan
            $additionalEmployees = $get('additional_employees_detail');
            if (is_array($additionalEmployees)) {
                $totalEmployees += count($additionalEmployees);
                
                foreach ($additionalEmployees as $empData) {
                    $empPocketMoney = (float)($empData['pocket_money_cost'] ?? 0);
                    $totalPocketMoney += ($empPocketMoney * $days);
                }
            }

            // 3. Biaya gabungan (Flat Total)
            $combinedOperational = (float)($get('accommodation_reserve_cost') ?: 0);

            $grandTotal = $combinedOperational + $totalPocketMoney;

            // Update state
            $set('trip_duration_days', $days);
            $set('total_employees', $totalEmployees);
            $set('total_cost', $grandTotal);
            $set('business_trip_expenses', $grandTotal);
            
            if ($accommodationNote) {
                // We can't easily push helper text but we can put it in a description or placeholder
            }
        } catch (\Exception $e) {
            // Log or ignore to prevent crash
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar & Destinasi')
                    ->description('Nomor registration, tujuan perjalanan, dan waktu perjalanan.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('registration_number')
                                    ->label('Nomor Surat (SPD)')
                                    ->default(fn () => \App\Services\LetterNumberService::generateBusinessTravelNumber())
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('pasal')
                                    ->label('Pasal/Dasar Hukum')
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Pasal 10 ayat 2'),

                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Tanggal Berangkat')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $set('end_date', null);
                                        self::updateTotals($get, $set);
                                    }),

                                Forms\Components\DatePicker::make('end_date')
                                    ->label('Tanggal Kembali')
                                    ->required()
                                    ->live()
                                    ->afterOrEqual('start_date')
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set)),
                            ]),

                        Forms\Components\TextInput::make('destination')
                            ->label('Kota/Tempat Tujuan')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Textarea::make('destination_detail')
                                    ->label('Detail Alamat Destinasi')
                                    ->rows(2)
                                    ->placeholder('Detail lokasi atau alamat lengkap'),

                                Forms\Components\Textarea::make('purpose_of_trip')
                                    ->label('Maksud Perjalanan')
                                    ->required()
                                    ->rows(2),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('shs_category')
                                    ->label('Kategori SHS Perjalanan')
                                    ->options([
                                        'DALAM WILAYAH PURBALINGGA (min. 15 Km)' => 'Dalam Wilayah Purbalingga',
                                        'PERJALANAN DINAS LUAR WILAYAH' => 'Perjalanan Dinas Luar Wilayah',
                                        'REPRESENTASI PERJALANAN DINAS' => 'Representasi Perjalanan Dinas',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('shs_location', null);
                                    }),

                                Forms\Components\Select::make('shs_location')
                                    ->label('Zona / Wilayah SHS')
                                    ->options(function (Get $get) {
                                        $category = $get('shs_category');
                                        if (!$category) return [];
                                        
                                        $locations = MasterStandarHargaSatuan::where('category', $category)
                                            ->distinct()
                                            ->pluck('location');
                                            
                                        $options = [];
                                        foreach ($locations as $loc) {
                                            $base = preg_match('/^ZONA\s+[IVXLCDM]+/i', $loc, $m) ? strtoupper($m[0]) : $loc;
                                            if (!isset($options[$base]) || strlen($loc) > strlen($options[$base])) {
                                                $options[$base] = $loc;
                                            }
                                        }
                                        return array_combine(array_values($options), array_values($options));
                                    })
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set)),
                            ]),
                    ]),

                Forms\Components\Section::make('Daftar Peserta & Biaya Perorangan')
                    ->description('Pilih pegawai yang melakukan perjalanan dan tentukan uang saku masing-masing.')
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Select::make('employee_id')
                                    ->label('Pegawai Utama')
                                    ->relationship('employee', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih pegawai utama')
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set)),

                                Forms\Components\TextInput::make('pocket_money_cost')
                                    ->label('Uang Saku Utama (per hari)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->visible(fn (Get $get) => filled($get('employee_id')))
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set)),
                            ])
                            ->columns(2),

                        Forms\Components\Repeater::make('additional_employees_detail')
                            ->label('Daftar Pegawai Tambahan (Pengikut)')
                            ->schema([
                                Forms\Components\Select::make('employee_id')
                                    ->label('Nama Pegawai')
                                    ->options(fn () => \App\Models\Employee::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if ($state) {
                                            $employee = Employee::with(['position', 'grade'])->find($state);
                                            if ($employee) {
                                                if ($employee->position) {
                                                    $set('position', $employee->position->name);
                                                }
                                                
                                                // Fetch SHS
                                                $shsCategory = $get('../../shs_category');
                                                $shsLocation = $get('../../shs_location');
                                                
                                                if ($shsCategory && $shsLocation) {
                                                    $mappedSpesifikasi = MasterStandarHargaSatuan::mapPositionToSpesifikasi(
                                                        $employee->position->name ?? '', 
                                                        $employee->grade->name ?? null
                                                    );

                                                    $shs = MasterStandarHargaSatuan::where('category', $shsCategory)
                                                        ->where('location', $shsLocation)
                                                        ->where('spesifikasi', $mappedSpesifikasi)
                                                        ->first();

                                                    if ($shs) {
                                                        $set('pocket_money_cost', $shs->amount);
                                                    }
                                                }
                                            }
                                        }
                                        self::updateTotals($get, $set);
                                    }),
                                Forms\Components\TextInput::make('position')
                                    ->label('Jabatan')
                                    ->required()
                                    ->readOnly()
                                    ->placeholder('Otomatis'),
                                Forms\Components\TextInput::make('pocket_money_cost')
                                    ->label('Uang Saku (per hari)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set)),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->addActionLabel('Tambah Pengikut')
                            ->defaultItems(0)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set)),
                    ]),

                Forms\Components\Section::make('Biaya Operasional & Ringkasan')
                    ->description('Biaya kolektif untuk seluruh tim dan ringkasan total biaya.')
                    ->schema([
                                Forms\Components\TextInput::make('accommodation_reserve_cost')
                                    ->label('Biaya Akomodasi & Cadangan (Total)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->helperText('Gabungan biaya Hotel dan Uang Cadangan (Input Manual)')
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set))
                                    ->columnSpanFull(),

                        Forms\Components\Placeholder::make('summary_review')
                            ->label('Detail Review Biaya')
                            ->content(function (Get $get) {
                                $days = (int)($get('trip_duration_days') ?: 0);
                                $employees = (int)($get('total_employees') ?: 0);
                                
                                // Re-calculate sub-totals for display
                                $mainPocket = (float)($get('pocket_money_cost') ?: 0);
                                $totalPocket = $mainPocket * $days;
                                
                                $additionals = $get('additional_employees_detail') ?: [];
                                if (is_array($additionals)) {
                                    foreach ($additionals as $emp) {
                                        $totalPocket += ((float)($emp['pocket_money_cost'] ?? 0) * $days);
                                    }
                                }
                                
                                $accReserve = (float)($get('accommodation_reserve_cost') ?: 0);
                                $grandTotal = $totalPocket + $accReserve;

                                return new \Illuminate\Support\HtmlString("
                                    <div class='bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm space-y-3'>
                                        <div class='flex justify-between items-center text-sm'>
                                            <span class='text-gray-500 dark:text-gray-400'>Total Uang Saku ($employees orang x $days hari)</span>
                                            <span class='font-medium text-gray-900 dark:text-gray-100 font-mono'>Rp " . number_format($totalPocket, 0, ',', '.') . "</span>
                                        </div>
                                        <div class='flex justify-between items-center text-sm'>
                                            <span class='text-gray-500 dark:text-gray-400'>Biaya Akomodasi & Cadangan (Manual)</span>
                                            <span class='font-medium text-gray-900 dark:text-gray-100 font-mono'>Rp " . number_format($accReserve, 0, ',', '.') . "</span>
                                        </div>
                                        <div class='pt-3 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center font-bold'>
                                            <span class='text-gray-900 dark:text-gray-100'>Estimasi Total Biaya Akhir</span>
                                            <span class='text-primary-600 dark:text-primary-400 text-lg font-mono'>Rp " . number_format($grandTotal, 0, ',', '.') . "</span>
                                        </div>
                                    </div>
                                ");
                            })
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('total_cost')
                            ->label('TOTAL BIAYA KESELURUHAN')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly()
                            ->dehydrated()
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'text-xl font-bold bg-gray-50 dark:bg-gray-800']),

                        Forms\Components\Hidden::make('trip_duration_days')->default(0),
                        Forms\Components\Hidden::make('total_employees')->default(0),
                        Forms\Components\Hidden::make('business_trip_expenses')->default(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Penandatangan')
                    ->schema([
                        Forms\Components\Toggle::make('is_manual_signatory')
                            ->label('Input Manual Penandatangan')
                            ->live()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && blank($record->signatory_employee_id) && !blank($record->signatory_name)) {
                                    $component->state(true);
                                }
                            })
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) $set('signatory_employee_id', null);
                                else { $set('signatory_name', null); $set('signatory_position', null); }
                            }),

                        Forms\Components\Group::make([
                            Forms\Components\Select::make('signatory_employee_id')
                                ->label('Pilih Penandatangan')
                                ->options(fn () => \App\Models\Employee::getSignatoryEmployees())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required(fn($get) => !$get('is_manual_signatory'))
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if ($state) {
                                        $employee = \App\Models\Employee::getSignatoryById($state);
                                        if ($employee) {
                                            $set('signatory_name', $employee->name);
                                            $set('signatory_position', $employee->position->name ?? '');
                                        }
                                    }
                                }),
                        ])->visible(fn($get) => !$get('is_manual_signatory')),

                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('signatory_name')
                                ->label('Nama (Manual)')
                                ->required(fn($get) => $get('is_manual_signatory')),
                            Forms\Components\TextInput::make('signatory_position')
                                ->label('Jabatan (Manual)')
                                ->required(fn($get) => $get('is_manual_signatory')),
                        ])->columns(2)->visible(fn($get) => $get('is_manual_signatory')),

                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan Tambahan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(true),

                Forms\Components\Section::make('Arsip Digital')
                    ->description('Upload SPPD yang sudah ditanda tangani dan stempel basah.')
                    ->schema([
                        Forms\Components\FileUpload::make('signed_file_path')
                            ->label('Upload SPPD (TTD & Stempel)')
                            ->disk('public')->visibility('public')
                            ->directory('business_travel_letters_signed')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(2048)
                            ->downloadable()
                            ->openable(),
                        Forms\Components\Select::make('status')
                            ->label('Status Surat')
                            ->options([
                                'on progress' => 'On Progress',
                                'selesai' => 'Selesai',
                            ])
                            ->default('on progress')
                            ->required()
                            ->native(false),
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

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'selesai' => 'success',
                        'on progress' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                    ->sortable(),

                Tables\Columns\IconColumn::make('signed_file_path')
                    ->label('Arsip Internal')
                    ->icon(fn($state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn($state): string => $state ? 'success' : 'danger'),

                Tables\Columns\IconColumn::make('archives')
                    ->label('Arsip (I/K)')
                    ->getStateUsing(fn($record) => (bool)$record->signed_file_path && (bool)$record->visit_file_path)
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-document-text')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn($record) => "Internal: " . ($record->signed_file_path ? '✅' : '❌') . " | Kunjungan: " . ($record->visit_file_path ? '✅' : '❌')),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => ($record->total_employees > 1 ? '+' . ($record->total_employees - 1) . ' pengikut' : 'Sendiri')),

                Tables\Columns\TextColumn::make('destination')
                    ->label('Tujuan')
                    ->searchable()
                    ->description(fn($record) => str($record->purpose_of_trip)->limit(30))
                    ->tooltip(fn($record) => $record->purpose_of_trip),

                Tables\Columns\TextColumn::make('timespan')
                    ->label('Waktu')
                    ->getStateUsing(fn($record) => $record->start_date->format('d/m/y') . ' - ' . $record->end_date->format('d/m/y'))
                    ->description(fn($record) => $record->trip_duration_days . ' hari'),

                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('travel_date')
                    ->label('Periode Perjalanan')
                    ->form([
                        Forms\Components\DatePicker::make('travel_from')->label('Dari'),
                        Forms\Components\DatePicker::make('travel_until')->label('Sampai'),
                    ])
                    ->query(fn(Builder $query, array $data) => $query
                        ->when($data['travel_from'], fn($q, $date) => $q->whereDate('start_date', '>=', $date))
                        ->when($data['travel_until'], fn($q, $date) => $q->whereDate('end_date', '<=', $date))),
                TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('cetak_report')
                    ->label('Cetak Report')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Sampai Tanggal'),
                        Forms\Components\Select::make('employee_id')
                            ->label('Filter Pegawai')
                            ->options(\App\Models\Employee::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data) {
                        $query = EmployeeBusinessTravelLetter::query();

                        if ($data['date_from']) {
                            $query->whereDate('start_date', '>=', $data['date_from']);
                        }
                        if ($data['date_until']) {
                            $query->whereDate('end_date', '<=', $data['date_until']);
                        }
                        if ($data['employee_id']) {
                            $query->where('employee_id', $data['employee_id']);
                        }

                        $records = $query->get();
                        $employeeName = $data['employee_id'] ? \App\Models\Employee::find($data['employee_id'])?->name : null;

                        $pdf = Pdf::loadView('pdf.report-summary', [
                            'title' => 'Surat Perjalanan Dinas (SPPD)',
                            'data' => $records,
                            'startDate' => $data['date_from'],
                            'endDate' => $data['date_until'],
                            'employeeName' => $employeeName,
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'Report_SPPD_' . now()->format('YmdHis') . '.pdf');
                    })
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                    Tables\Actions\Action::make('generate_pdf')
                        ->label('Cetak PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (EmployeeBusinessTravelLetter $record) {
                            $signatoryName = $record->signatory_name;
                            $signatoryPosition = $record->signatory_position;

                            // Priority 1: Data dari relasi baru (signatory_employee_id)
                            if (blank($signatoryName) && $record->signatoryEmployee) {
                                $signatoryName = $record->signatoryEmployee->name;
                                $signatoryPosition = $record->signatoryEmployee->position->name ?? '';
                            }
                            
                            // Priority 2: Data dari relasi lama (employee_signatory_id)
                            if (blank($signatoryName) && $record->signatory) {
                                $signatoryName = $record->signatory->name;
                                $signatoryPosition = $record->signatory->position->name ?? '';
                            }

                            // Priority 3: Fallback ke default
                            $signatoryName = $signatoryName ?: 'Direktur PERUMDA';
                            $signatoryPosition = $signatoryPosition ?: 'Direktur';

                            $pdf = Pdf::loadView('pdf.business-travel-letter', [
                                'travel' => $record,
                                'signatory_name' => $signatoryName,
                                'signatory_position' => $signatoryPosition
                            ]);

                            $filename = 'SPD_' . str_replace(['/', '\\', ' '], '_', $record->registration_number) . '.pdf';
                            $pdfPath = 'business_travel_letters/' . $filename;
                            $fullPath = storage_path('app/public/' . $pdfPath);

                            if (!file_exists(dirname($fullPath))) mkdir(dirname($fullPath), 0755, true);
                            $pdf->save($fullPath);
                            $record->update(['pdf_file_path' => $pdfPath]);

                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, $filename);
                        }),
                    Tables\Actions\Action::make('upload_signed')
                        ->label('Arsip TTD Internal')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->form([
                            Forms\Components\FileUpload::make('signed_file_path')
                                ->label('File Scan TTD Internal (PDF)')
                                ->disk('public')
                                ->visibility('public')
                                ->directory('business_travel_letters_internal')
                                ->acceptedFileTypes(['application/pdf'])
                                ->required(),
                        ])
                        ->action(function (EmployeeBusinessTravelLetter $record, array $data) {
                            $record->update([
                                'signed_file_path' => $data['signed_file_path'],
                            ]);
                        })
                        ->visible(fn(EmployeeBusinessTravelLetter $record) => empty($record->signed_file_path)),
                    Tables\Actions\Action::make('selesaikan')
                        ->label('Selesaikan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\FileUpload::make('visit_file_path')
                                ->label('File Scan Cap Kunjungan (PDF)')
                                ->disk('public')->visibility('public')
                                ->directory('business_travel_letters_complete')
                                ->acceptedFileTypes(['application/pdf'])
                                ->required(),
                        ])
                        ->action(function (EmployeeBusinessTravelLetter $record, array $data) {
                            $record->update([
                                'visit_file_path' => $data['visit_file_path'],
                                'status' => 'selesai',
                            ]);
                        })
                        ->visible(fn(EmployeeBusinessTravelLetter $record) => !empty($record->signed_file_path) && $record->status !== 'selesai'),
                    Tables\Actions\Action::make('view_signed')
                        ->label('Lihat Arsip')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->form([
                            Forms\Components\Group::make([
                                Forms\Components\Placeholder::make('internal_file')
                                    ->label('Arsip TTD Internal')
                                    ->content(fn($record) => $record->signed_file_path ? new \Illuminate\Support\HtmlString("<a href='".asset('storage/'.$record->signed_file_path)."' target='_blank' class='text-primary-600 underline'>Buka File Internal</a>") : 'Belum diupload'),
                                Forms\Components\Placeholder::make('visit_file')
                                    ->label('Arsip Cap Kunjungan')
                                    ->content(fn($record) => $record->visit_file_path ? new \Illuminate\Support\HtmlString("<a href='".asset('storage/'.$record->visit_file_path)."' target='_blank' class='text-primary-600 underline'>Buka File Kunjungan</a>") : 'Belum diupload'),
                            ])->columns(2)
                        ])
                        ->visible(fn(EmployeeBusinessTravelLetter $record) => !empty($record->signed_file_path)),
                    Tables\Actions\DeleteAction::make(),
                ])
                ->label('Aksi')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeBusinessTravelLetters::route('/'),
            'create' => Pages\CreateEmployeeBusinessTravelLetter::route('/create'),
            'edit' => Pages\EditEmployeeBusinessTravelLetter::route('/{record}/edit'),
        ];
    }
}
