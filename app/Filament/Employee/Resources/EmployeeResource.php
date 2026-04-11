<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeResource\Pages;
use App\Filament\Employee\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use App\Models\MasterDepartment;
use App\Models\MasterEmployeePosition;
use App\Models\MasterEmployeeGrade;
use App\Models\MasterEmployeeEducation;
use App\Models\MasterEmployeeStatusEmployment;
use App\Models\MasterEmployeeAgreement;
use App\Models\MasterEmployeeFamily;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Pegawai';
    protected static ?string $modelLabel = 'Pegawai';
    protected static ?string $pluralModelLabel = 'Pegawai';
    protected static ?string $navigationGroup = 'Manajemen Pegawai';
    protected static ?int $navigationSort = 201;

    public static function getModelLabel(): string
    {
        return 'Pegawai';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pegawai';
    }

    public static function generatePDFReport()
    {
        // Generate PDF menggunakan DomPDF dengan orientasi landscape
        $pdf = Pdf::loadView('reports.employee-report', [
            'data' => static::getReportData(),
            'generated_at' => now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s')
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Report_Pegawai_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    public static function generateFilteredPDFReport($departmentId = null, $positionId = null, $statusId = null, $educationId = null)
    {
        // Generate PDF dengan filter menggunakan DomPDF dengan orientasi landscape
        $pdf = Pdf::loadView('reports.employee-report', [
            'data' => static::getFilteredReportData($departmentId, $positionId, $statusId, $educationId),
            'generated_at' => now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
            'filters' => [
                'department' => $departmentId ? \App\Models\MasterDepartment::find($departmentId)?->name : 'Semua',
                'position' => $positionId ? \App\Models\MasterEmployeePosition::find($positionId)?->name : 'Semua',
                'status' => $statusId ? \App\Models\MasterEmployeeStatusEmployment::find($statusId)?->name : 'Semua',
                'education' => $educationId ? \App\Models\MasterEmployeeEducation::find($educationId)?->name : 'Semua',
            ]
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Report_Pegawai_Filtered_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    protected static function getFilteredReportData($departmentId = null, $positionId = null, $statusId = null, $educationId = null)
    {
        $query = Employee::with([
            'department',
            'subDepartment',
            'position',
            'employmentStatus',
            'education',
            'grade'
        ]);        // Apply filters
        if ($departmentId) {
            $query->where('departments_id', $departmentId);
        }
        if ($positionId) {
            $query->where('employee_position_id', $positionId);
        }
        if ($statusId) {
            $query->where('employment_status_id', $statusId);
        }
        if ($educationId) {
            $query->where('employee_education_id', $educationId);
        }

        $employees = $query->get();

        // Hitung statistik berdasarkan status kepegawaian
        $statusStats = [
            'permanent' => $employees->filter(function ($employee) {
                return $employee->employmentStatus &&
                    stripos($employee->employmentStatus->name, 'tetap') !== false;
            })->count(),
            'contract' => $employees->filter(function ($employee) {
                return $employee->employmentStatus &&
                    stripos($employee->employmentStatus->name, 'kontrak') !== false;
            })->count(),
            'probation' => $employees->filter(function ($employee) {
                return $employee->employmentStatus &&
                    stripos($employee->employmentStatus->name, 'percobaan') !== false;
            })->count(),
        ];

        // Hitung statistik berdasarkan departemen
        $departmentStats = $employees->groupBy('department.name')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(5); // Ambil 5 departemen terbesar

        return [
            'employees' => $employees,
            'status_stats' => $statusStats,
            'department_stats' => $departmentStats,
            'total_employees' => $employees->count(),
        ];
    }

    protected static function getReportData()
    {
        $employees = Employee::with([
            'department',
            'subDepartment',
            'position',
            'employmentStatus',
            'education'
        ])->get();        // Hitung statistik berdasarkan status kepegawaian
        $statusStats = [
            'permanent' => $employees->filter(function ($employee) {
                return $employee->employmentStatus &&
                    stripos($employee->employmentStatus->name, 'tetap') !== false;
            })->count(),
            'contract' => $employees->filter(function ($employee) {
                return $employee->employmentStatus &&
                    stripos($employee->employmentStatus->name, 'kontrak') !== false;
            })->count(),
            'probation' => $employees->filter(function ($employee) {
                return $employee->employmentStatus &&
                    stripos($employee->employmentStatus->name, 'percobaan') !== false;
            })->count(),
        ];

        // Hitung statistik berdasarkan departemen
        $departmentStats = $employees->groupBy('department.name')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(5); // Ambil 5 departemen terbesar

        return [
            'employees' => $employees,
            'status_stats' => $statusStats,
            'department_stats' => $departmentStats,
            'total_employees' => $employees->count(),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Informasi Pegawai')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Data Personal')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Forms\Components\Section::make('Informasi Pribadi')
                                    ->description('Data dasar pribadi Pegawai')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\FileUpload::make('image')
                                                    ->label('Foto Pas')
                                                    ->image()
                                                    ->avatar()
                                                    ->disk('public')
                                                    ->directory('employees/photos')
                                                    ->columnSpan(1),
                                                Forms\Components\Group::make()
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Nama Lengkap')
                                                            ->required()
                                                            ->maxLength(255),
                                                        Forms\Components\TextInput::make('nippam')
                                                            ->label('NIPPAM')
                                                            ->unique(ignoreRecord: true)
                                                            ->maxLength(50),
                                                    ])->columnSpan(1),
                                                Forms\Components\Select::make('gender')
                                                    ->label('Jenis Kelamin')
                                                    ->options([
                                                        'male' => 'Laki-laki',
                                                        'female' => 'Perempuan',
                                                    ])
                                                    ->required()
                                                    ->placeholder('Pilih jenis kelamin'),
                                                Forms\Components\DatePicker::make('date_birth')
                                                    ->label('Tanggal Lahir')
                                                    ->required()
                                                    ->before('today')
                                                    ->helperText('Tanggal lahir harus sebelum hari ini'),
                                                Forms\Components\TextInput::make('place_birth')
                                                    ->label('Tempat Lahir')
                                                    ->required()
                                                    ->placeholder('Contoh: Jakarta')
                                                    ->maxLength(255),
                                                Forms\Components\Select::make('marital_status')
                                                    ->label('Status Perkawinan')
                                                    ->options([
                                                        'single' => 'Belum Menikah',
                                                        'married' => 'Menikah',
                                                        'divorced' => 'Cerai',
                                                        'widowed' => 'Janda/Duda',
                                                    ])
                                                    ->required()
                                                    ->placeholder('Pilih status perkawinan'),
                                            ]),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Kontak & Identitas')
                            ->icon('heroicon-m-identification')
                            ->schema([
                                Forms\Components\Section::make('Informasi Kontak & Identitas')
                                    ->description('Data kontak dan dokumen identitas')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('address')
                                                    ->label('Alamat Lengkap')
                                                    ->placeholder('Masukkan alamat lengkap sesuai KTP')
                                                    ->maxLength(500)
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('phone_number')
                                                    ->label('Nomor Telepon')
                                                    ->tel()
                                                    ->numeric()
                                                    ->rules(['regex:/^[0-9+\-\s()]+$/'])
                                                    ->placeholder('Contoh: 081234567890')
                                                    ->helperText('Hanya boleh angka, +, -, spasi, dan kurung')
                                                    ->maxLength(20),
                                                Forms\Components\TextInput::make('id_number')
                                                    ->label('NIK (KTP)')
                                                    ->numeric()
                                                    ->rules(['digits:16'])
                                                    ->placeholder('1234567890123456')
                                                    ->helperText('16 digit nomor KTP')
                                                    ->maxLength(16),
                                                Forms\Components\TextInput::make('email')
                                                    ->label('Email')
                                                    ->email()
                                                    ->rules(['email:rfc,dns'])
                                                    ->placeholder('contoh@email.com')
                                                    ->helperText('Format email yang valid')
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('familycard_number')
                                                    ->label('Nomor Kartu Keluarga')
                                                    ->numeric()
                                                    ->rules(['digits:16'])
                                                    ->placeholder('1234567890123456')
                                                    ->helperText('16 digit nomor Kartu Keluarga')
                                                    ->maxLength(16),
                                            ]),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Keuangan & Asuransi')
                            ->icon('heroicon-m-banknotes')
                            ->schema([
                                Forms\Components\Section::make('Informasi Perbankan & Asuransi')
                                    ->description('Data keuangan dan asuransi')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('bank_account_number')
                                                    ->label('Nomor Rekening Bank')
                                                    ->numeric()
                                                    ->rules(['regex:/^[0-9\-]+$/'])
                                                    ->placeholder('1234567890')
                                                    ->helperText('Hanya boleh angka dan tanda strip (-)')
                                                    ->maxLength(25),
                                                Forms\Components\TextInput::make('bpjs_tk_number')
                                                    ->label('Nomor BPJS Ketenagakerjaan')
                                                    ->numeric()
                                                    ->rules(['regex:/^[0-9]+$/'])
                                                    ->placeholder('12345678901')
                                                    ->helperText('Hanya boleh angka')
                                                    ->maxLength(15),
                                                Forms\Components\TextInput::make('bpjs_kes_number')
                                                    ->label('Nomor BPJS Kesehatan')
                                                    ->numeric()
                                                    ->rules(['regex:/^[0-9]+$/'])
                                                    ->placeholder('0001234567890')
                                                    ->helperText('13 digit nomor BPJS Kesehatan')
                                                    ->maxLength(13),
                                                Forms\Components\TextInput::make('rek_dplk_pribadi')
                                                    ->label('Rekening DPLK Pribadi')
                                                    ->numeric()
                                                    ->rules(['regex:/^[0-9\-]+$/'])
                                                    ->placeholder('1234567890')
                                                    ->helperText('Hanya boleh angka')
                                                    ->maxLength(20),
                                                Forms\Components\TextInput::make('rek_dplk_bersama')
                                                    ->label('Rekening DPLK Bersama')
                                                    ->numeric()
                                                    ->rules(['regex:/^[0-9\-]+$/'])
                                                    ->placeholder('1234567890')
                                                    ->helperText('Hanya boleh angka')
                                                    ->maxLength(20),
                                                Forms\Components\TextInput::make('npwp_number')
                                                    ->label('Nomor NPWP')
                                                    ->rules(['regex:/^[0-9]{2}\.[0-9]{3}\.[0-9]{3}\.[0-9]\-[0-9]{3}\.[0-9]{3}$/'])
                                                    ->placeholder('12.345.678.9-012.345')
                                                    ->helperText('Format: XX.XXX.XXX.X-XXX.XXX')
                                                    ->maxLength(20),
                                            ]),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Kepegawaian')
                            ->icon('heroicon-m-briefcase')
                            ->schema([
                                Forms\Components\Section::make('Detail Kepegawaian')
                                    ->description('Informasi pekerjaan dan struktur organisasi')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Radio::make('work_unit_type')
                                                    ->label('Tipe Unit Kerja')
                                                    ->options([
                                                        'bagian' => 'Bagian (Pusat)',
                                                        'cabang' => 'Cabang (Wilayah)',
                                                        'unit' => 'Unit (Wilayah)',
                                                    ])
                                                    ->live()
                                                    ->afterStateHydrated(function (Forms\Components\Radio $component, $state, ?Employee $record) {
                                                        if (!$record) return;
                                                        if ($record->unit_id) $component->state('unit');
                                                        elseif ($record->cabang_id) $component->state('cabang');
                                                        elseif ($record->bagian_id) $component->state('bagian');
                                                    })
                                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                        $set('bagian_id', null);
                                                        $set('cabang_id', null);
                                                        $set('unit_id', null);
                                                        $set('departments_id', null);
                                                    })
                                                    ->dehydrated(false)
                                                    ->columnSpanFull(),

                                                Forms\Components\Select::make('bagian_id')
                                                    ->label('Pilih Bagian')
                                                    ->options(\App\Models\MasterDepartment::bagian()->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->visible(fn (Forms\Get $get) => $get('work_unit_type') === 'bagian')
                                                    ->live()
                                                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('departments_id', $state)),

                                                Forms\Components\Select::make('cabang_id')
                                                    ->label('Pilih Cabang')
                                                    ->options(\App\Models\MasterDepartment::cabang()->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->visible(fn (Forms\Get $get) => $get('work_unit_type') === 'cabang')
                                                    ->live()
                                                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('departments_id', $state)),

                                                Forms\Components\Select::make('unit_id')
                                                    ->label('Pilih Unit')
                                                    ->options(\App\Models\MasterDepartment::unit()->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->visible(fn (Forms\Get $get) => $get('work_unit_type') === 'unit')
                                                    ->live()
                                                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('departments_id', $state)),

                                                Forms\Components\Hidden::make('departments_id'),
                                                Forms\Components\Select::make('employee_position_id')
                                                    ->label('Posisi/Jabatan')
                                                    ->relationship('position', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                                Forms\Components\Group::make([
                                                    Forms\Components\Select::make('basic_salary_id')
                                                        ->label('Golongan')
                                                        ->relationship('grade', 'name')
                                                        ->live()
                                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                            $set('employee_service_grade_id', null);
                                                        })
                                                        ->searchable()
                                                        ->preload()
                                                        ->required(),
                                                    Forms\Components\Select::make('employee_service_grade_id')
                                                        ->label('Masa Kerja Golongan (MKG)')
                                                        ->options(\App\Models\MasterEmployeeServiceGrade::all()->pluck('desc', 'id'))
                                                        ->live()
                                                        ->searchable()
                                                        ->preload()
                                                        ->required(),
                                                    Forms\Components\Placeholder::make('calculated_salary')
                                                        ->label('Gaji Pokok Terhitung')
                                                        ->content(function (Forms\Get $get) {
                                                            $gradeId = $get('basic_salary_id');
                                                            $mkgId = $get('employee_service_grade_id');
                                                            if ($gradeId && $mkgId) {
                                                                $salary = \App\Models\MasterEmployeeBasicSalary::where('employee_grade_id', $gradeId)
                                                                    ->where('employee_service_grade_id', $mkgId)
                                                                    ->first();
                                                                return $salary ? 'Rp ' . number_format($salary->amount, 0, ',', '.') : 'Data gaji belum diinput';
                                                            }
                                                            return 'Pilih Golongan dan MKG';
                                                        }),
                                                ])->columns(3)->columnSpanFull(),
                                                Forms\Components\Select::make('employee_education_id')
                                                    ->label('Tingkat Pendidikan')
                                                    ->relationship('education', 'name')
                                                    ->searchable()
                                                    ->preload(),
                                                Forms\Components\Select::make('employment_status_id')
                                                    ->label('Status Kepegawaian')
                                                    ->relationship('employmentStatus', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                                Forms\Components\Select::make('master_employee_agreement_id')
                                                    ->label('Jenis Kontrak')
                                                    ->relationship('agreement', 'name')
                                                    ->searchable()
                                                    ->preload(),
                                            ]),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Tanggal & Lainnya')
                            ->icon('heroicon-m-calendar-days')
                            ->schema([
                                Forms\Components\Section::make('Tanggal Penting & Informasi Tambahan')
                                    ->description('Tanggal-tanggal penting dan detail tambahan')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\DatePicker::make('entry_date')
                                                    ->label('Tanggal Masuk')
                                                    ->required()
                                                    ->helperText('Tanggal mulai bekerja'),
                                                Forms\Components\DatePicker::make('probation_appointment_date')
                                                    ->label('Tanggal Pengangkatan Tetap')
                                                    ->after('entry_date')
                                                    ->helperText('Username, tanggal pensiun, dan masa kerja akan otomatis dibuat'),
                                                Forms\Components\TextInput::make('leave_balance')
                                                    ->label('Saldo Cuti')
                                                    ->numeric()
                                                    ->default(12)
                                                    ->helperText('Default 12 hari per tahun'),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto Pas')
                    ->circular()
                    ->disk('public'),
                Tables\Columns\TextColumn::make('nippam')
                    ->label('NIPPAM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_number')
                    ->label('NIK (KTP)')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('unit_kerja')
                    ->label('Unit Kerja')
                    ->getStateUsing(function (Employee $record) {
                        return $record->active_organizational_unit->name ?? '-';
                    })
                    ->description(function (Employee $record) {
                        return $record->active_organizational_unit->type ?? '';
                    })
                    ->searchable(['department.name', 'bagian.name', 'cabang.name', 'unit.name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Golongan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('serviceGrade.service_grade')
                    ->label('MKG (Thn)')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('basic_salary_amount')
                    ->label('Gaji Pokok')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('position_status')
                    ->label('Posisi / Status')
                    ->getStateUsing(function (Employee $record) {
                        $position = $record->position->name ?? '-';
                        $status = $record->employmentStatus->name ?? '-';
                        return $position . "\n" . $status;
                    })
                    ->html()
                    ->formatStateUsing(function ($state) {
                        $lines = explode("\n", $state);
                        return '<div class="leading-tight">' .
                            '<div class="font-semibold text-gray-900 dark:text-gray-100">' . $lines[0] . '</div>' .
                            '<div class="inline-block px-2 py-1 mt-1 text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">' . $lines[1] . '</div>' .
                            '</div>';
                    })
                    ->searchable(['position.name', 'employmentStatus.name']),
                Tables\Columns\TextColumn::make('contact_info')
                    ->label('Email / No. Telp')
                    ->getStateUsing(function (Employee $record) {
                        $email = $record->email ?? '-';
                        $phone = $record->phone_number ?? '-';
                        return $email . "\n" . $phone;
                    })
                    ->html()
                    ->formatStateUsing(function ($state) {
                        $lines = explode("\n", $state);
                        return '<div class="leading-tight">' .
                            '<div class="font-semibold text-gray-900 dark:text-gray-100 text-sm">' . $lines[0] . '</div>' .
                            '<div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">' . $lines[1] . '</div>' .
                            '</div>';
                    })
                    ->searchable(['email', 'phone_number'])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('birth_info')
                    ->label('Tempat, Tanggal Lahir')
                    ->getStateUsing(function (Employee $record) {
                        $place = $record->place_birth ?? '-';
                        $date = $record->date_birth ? \Carbon\Carbon::parse($record->date_birth)->format('d/m/Y') : '-';
                        return $place . "\n" . $date;
                    })
                    ->html()
                    ->formatStateUsing(function ($state) {
                        $lines = explode("\n", $state);
                        return '<div class="leading-tight">' .
                            '<div class="font-semibold text-gray-900 dark:text-gray-100 text-sm">' . $lines[0] . '</div>' .
                            '<div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">' . $lines[1] . '</div>' .
                            '</div>';
                    })
                    ->searchable(['place_birth', 'date_birth'])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('formatted_length_service')
                    ->label('Masa Kerja')
                    ->getStateUsing(fn(Employee $record) => $record->formatted_length_service)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('probation_appointment_date', $direction === 'asc' ? 'desc' : 'asc');
                    })
                    ->icon('heroicon-m-clock')
                    ->placeholder('Belum ada data'),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Tanggal Masuk')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('data_completeness')
                    ->label('Kelengkapan Data')
                    ->badge()
                    ->getStateUsing(fn(Employee $record) => $record->getDataCompletenessPercentage() . '%')
                    ->color(fn(Employee $record): string => match (true) {
                        $record->getDataCompletenessPercentage() >= 90 => 'success',
                        $record->getDataCompletenessPercentage() >= 70 => 'warning',
                        default => 'danger',
                    })
                    ->tooltip(
                        fn(Employee $record) => $record->hasIncompleteData()
                            ? 'Data yang belum lengkap: ' . implode(', ', $record->getMissingDataFields())
                            : 'Data sudah lengkap'
                    ),
            ])
            ->headerActions([
                Tables\Actions\Action::make('generate_report')
                    ->label('Cetak Report PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->modalHeading('Filter Report Pegawai')
                    ->modalSubheading('Pilih filter untuk report yang akan digenerate')
                    ->modalWidth('4xl')
                    ->form([
                        Forms\Components\Section::make('Filter Report')
                            ->description('Pilih filter untuk menyaring data Pegawai yang akan ditampilkan dalam report')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('department_filter')
                                            ->label('Departemen')
                                            ->options(\App\Models\MasterDepartment::pluck('name', 'id')->toArray())
                                            ->searchable()
                                            ->placeholder('Semua Departemen'),
                                        Forms\Components\Select::make('position_filter')
                                            ->label('Posisi/Jabatan')
                                            ->options(\App\Models\MasterEmployeePosition::pluck('name', 'id')->toArray())
                                            ->searchable()
                                            ->placeholder('Semua Posisi'),
                                        Forms\Components\Select::make('status_filter')
                                            ->label('Status Kepegawaian')
                                            ->options(\App\Models\MasterEmployeeStatusEmployment::pluck('name', 'id')->toArray())
                                            ->searchable()
                                            ->placeholder('Semua Status'),
                                        Forms\Components\Select::make('education_filter')
                                            ->label('Tingkat Pendidikan')
                                            ->options(\App\Models\MasterEmployeeEducation::pluck('name', 'id')->toArray())
                                            ->searchable()
                                            ->placeholder('Semua Tingkat Pendidikan'),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data) {
                        return static::generateFilteredPDFReport(
                            $data['department_filter'] ?? null,
                            $data['position_filter'] ?? null,
                            $data['status_filter'] ?? null,
                            $data['education_filter'] ?? null
                        );
                    })
                    ->tooltip('Generate laporan Pegawai dalam format PDF dengan filter'),

                Tables\Actions\Action::make('download_template')
                    ->label('Download Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function () {
                        $headers = [
                            'nippam',
                            'name',
                            'gender',
                            'place_birth',
                            'date_birth',
                            'religion',
                            'marital_status',
                            'email',
                            'phone_number',
                            'id_number',
                            'familycard_number',
                            'npwp_number',
                            'bank_account_number',
                            'bpjs_tk_number',
                            'bpjs_kes_number',
                            'rek_dplk_pribadi',
                            'rek_dplk_bersama',
                            'address',
                            'department_name',
                            'position_name',
                            'employment_status_name'
                        ];

                        $callback = function () use ($headers) {
                            $file = fopen('php://output', 'w');
                            fputcsv($file, $headers);
                            // Add example row
                            fputcsv($file, [
                                '12345',
                                'Nama Pegawai',
                                'male',
                                'Jakarta',
                                '1990-01-01',
                                'Islam',
                                'single',
                                'email@example.com',
                                '08123456789',
                                '1234567890123456',
                                '1234567890123456',
                                '12.345.678.9-012.345',
                                '1234567890',
                                '12345678901',
                                '0001234567890',
                                '1234567890',
                                '1234567890',
                                'Alamat Lengkap',
                                'Bagian Umum',
                                'Staff',
                                'Pegawai Tetap'
                            ]);
                            fclose($file);
                        };

                        return response()->streamDownload($callback, 'template_import_pegawai.csv');
                    }),

                Tables\Actions\Action::make('import_pegawai')
                    ->label('Import CSV')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->form([
                        Forms\Components\FileUpload::make('csv_file')
                            ->label('File CSV')
                            ->required()
                            ->acceptedFileTypes(['text/csv', 'application/csv']),
                    ])
                    ->action(function (array $data) {
                        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($data['csv_file']);
                        $file = fopen($path, 'r');
                        $header = fgetcsv($file);

                        $count = 0;
                        while (($row = fgetcsv($file)) !== false) {
                            $rowData = array_combine($header, $row);

                            // Find IDs from names
                            $dept = \App\Models\MasterDepartment::where('name', 'LIKE', '%' . ($rowData['department_name'] ?? '') . '%')->first();
                            $pos = \App\Models\MasterEmployeePosition::where('name', 'LIKE', '%' . ($rowData['position_name'] ?? '') . '%')->first();
                            $status = \App\Models\MasterEmployeeStatusEmployment::where('name', 'LIKE', '%' . ($rowData['employment_status_name'] ?? '') . '%')->first();

                            Employee::updateOrCreate(
                                [
                                    'nippam' => $rowData['nippam'],
                                ],
                                [
                                    'name' => $rowData['name'],
                                    'gender' => $rowData['gender'],
                                    'religion' => $rowData['religion'] ?? null,
                                    'place_birth' => $rowData['place_birth'],
                                    'date_birth' => $rowData['date_birth'],
                                    'marital_status' => $rowData['marital_status'],
                                    'email' => $rowData['email'],
                                    'phone_number' => $rowData['phone_number'],
                                    'id_number' => $rowData['id_number'],
                                    'familycard_number' => $rowData['familycard_number'] ?? null,
                                    'npwp_number' => $rowData['npwp_number'] ?? null,
                                    'bank_account_number' => $rowData['bank_account_number'] ?? null,
                                    'bpjs_tk_number' => $rowData['bpjs_tk_number'] ?? null,
                                    'bpjs_kes_number' => $rowData['bpjs_kes_number'] ?? null,
                                    'rek_dplk_pribadi' => $rowData['rek_dplk_pribadi'] ?? null,
                                    'rek_dplk_bersama' => $rowData['rek_dplk_bersama'] ?? null,
                                    'address' => $rowData['address'],
                                    'departments_id' => $dept?->id,
                                    'employee_position_id' => $pos?->id,
                                    'employment_status_id' => $status?->id,
                                    'entry_date' => now(),
                                    'users_id' => auth()->id() ?? 1,
                                ]
                            );
                            $count++;
                        }
                        fclose($file);

                        Notification::make()
                            ->title($count . ' data pegawai berhasil diimport')
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('departments_id')
                    ->relationship('department', 'name')
                    ->label('Departemen')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('employee_position_id')
                    ->relationship('position', 'name')
                    ->label('Posisi/Jabatan')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('employment_status_id')
                    ->relationship('employmentStatus', 'name')
                    ->label('Status Kepegawaian'),
                Tables\Filters\Filter::make('incomplete_data')
                    ->label('Data Tidak Lengkap')
                    ->query(fn(Builder $query) => $query->where(function ($q) {
                        $q->whereNull('id_number')
                            ->orWhereNull('familycard_number')
                            ->orWhereNull('bank_account_number')
                            ->orWhereNull('bpjs_kes_number')
                            ->orWhereNull('bpjs_tk_number')
                            ->orWhereNull('rek_dplk_pribadi')
                            ->orWhereNull('rek_dplk_bersama')
                            ->orWhereNull('employee_education_id')
                            ->orWhereNull('probation_appointment_date');
                        // retirement, username, length_service dihilangkan karena otomatis
                    })),
                Tables\Filters\Filter::make('complete_data')
                    ->label('Data Lengkap')
                    ->query(fn(Builder $query) => $query->where(function ($q) {
                        $q->whereNotNull('id_number')
                            ->whereNotNull('familycard_number')
                            ->whereNotNull('bank_account_number')
                            ->whereNotNull('bpjs_kes_number')
                            ->whereNotNull('bpjs_tk_number')
                            ->whereNotNull('rek_dplk_pribadi')
                            ->whereNotNull('rek_dplk_bersama')
                            ->whereNotNull('employee_education_id')
                            ->whereNotNull('probation_appointment_date');
                        // retirement, username, length_service dihilangkan karena otomatis
                    })),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('print')
                        ->label('Cetak Profil')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn(Employee $record): string => route('employees.print', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('update_incomplete_data')
                        ->label('Lengkapi Data')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('warning')
                        ->visible(fn(Employee $record) => $record->hasIncompleteData())
                        ->modalHeading(fn(Employee $record) => 'Lengkapi Data - ' . $record->name)
                        ->modalSubheading('Silakan lengkapi data yang masih kosong')
                        ->modalWidth('6xl')
                        ->form([
                            Forms\Components\Section::make('Data Identitas')
                                ->description('Informasi identitas kependudukan')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('id_number')
                                                ->label('NIK (KTP)')
                                                ->numeric()
                                                ->rules(['digits:16'])
                                                ->placeholder('1234567890123456')
                                                ->helperText('16 digit nomor KTP')
                                                ->maxLength(16),
                                            Forms\Components\TextInput::make('familycard_number')
                                                ->label('Nomor Kartu Keluarga')
                                                ->numeric()
                                                ->rules(['digits:16'])
                                                ->placeholder('1234567890123456')
                                                ->helperText('16 digit nomor Kartu Keluarga')
                                                ->maxLength(16),
                                        ]),
                                ]),
                            Forms\Components\Section::make('Data Finansial')
                                ->description('Informasi rekening dan BPJS')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('bank_account_number')
                                                ->label('Nomor Rekening Bank')
                                                ->numeric()
                                                ->rules(['regex:/^[0-9\-]+$/'])
                                                ->placeholder('1234567890')
                                                ->helperText('Hanya boleh angka dan tanda strip (-)')
                                                ->maxLength(25),
                                            Forms\Components\TextInput::make('bpjs_kes_number')
                                                ->label('BPJS Kesehatan')
                                                ->numeric()
                                                ->rules(['regex:/^[0-9]+$/'])
                                                ->placeholder('0001234567890')
                                                ->helperText('13 digit nomor BPJS Kesehatan')
                                                ->maxLength(13),
                                        ]),
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\TextInput::make('bpjs_tk_number')
                                                ->label('BPJS Ketenagakerjaan')
                                                ->numeric()
                                                ->rules(['regex:/^[0-9]+$/'])
                                                ->placeholder('12345678901')
                                                ->helperText('Hanya boleh angka')
                                                ->maxLength(15),
                                            Forms\Components\TextInput::make('rek_dplk_pribadi')
                                                ->label('DPLK Pribadi')
                                                ->numeric()
                                                ->rules(['regex:/^[0-9\-]+$/'])
                                                ->placeholder('1234567890')
                                                ->helperText('Hanya boleh angka')
                                                ->maxLength(20),
                                            Forms\Components\TextInput::make('rek_dplk_bersama')
                                                ->label('DPLK Bersama')
                                                ->numeric()
                                                ->rules(['regex:/^[0-9\-]+$/'])
                                                ->placeholder('1234567890')
                                                ->helperText('Hanya boleh angka')
                                                ->maxLength(20),
                                        ]),
                                ]),
                            Forms\Components\Section::make('Data Kepegawaian')
                                ->description('Informasi career dan masa kerja')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Select::make('employee_education_id')
                                                ->label('Education Level')
                                                ->relationship('education', 'name')
                                                ->placeholder('Pilih tingkat pendidikan')
                                                ->searchable()
                                                ->preload(),
                                            Forms\Components\DatePicker::make('probation_appointment_date')
                                                ->label('Probation Appointment Date')
                                                ->placeholder('Pilih tanggal pengangkatan tetap')
                                                ->helperText('Retirement date dan length of service akan otomatis dihitung'),
                                        ]),
                                ]),
                        ])
                        ->action(function (Employee $record, array $data): void {
                            $filteredData = array_filter($data, function ($value) {
                                return $value !== null && $value !== '';
                            });
                            $record->update($filteredData);
                            Notification::make()
                                ->title('Data berhasil diperbarui')
                                ->body('Data tambahan berhasil disimpan')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Pegawai')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\Group::make([
                                    Infolists\Components\ImageEntry::make('image')
                                        ->hiddenLabel()
                                        ->circular()
                                        ->height(250)
                                        ->disk('public')
                                        ->extraAttributes([
                                            'class' => 'flex justify-center',
                                        ]),
                                ])->columnSpan(1),
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('nippam')
                                        ->label('NIPPAM')
                                        ->weight('bold'),
                                    Infolists\Components\TextEntry::make('name')
                                        ->label('Nama Lengkap')
                                        ->weight('bold')
                                        ->size('lg'),
                                    Infolists\Components\TextEntry::make('position.name')
                                        ->label('Jabatan')
                                        ->color('primary'),
                                    Infolists\Components\TextEntry::make('employmentStatus.name')
                                        ->label('Status Kepegawaian')
                                        ->badge()
                                        ->color('success'),
                                ])->columnSpan(2),
                            ]),
                    ]),

                Infolists\Components\Grid::make(3)
                    ->schema([
                        Infolists\Components\Section::make('Data Pribadi')
                            ->schema([
                                Infolists\Components\TextEntry::make('id_number')
                                    ->label('NIK (KTP)'),
                                Infolists\Components\TextEntry::make('gender')
                                    ->label('Jenis Kelamin')
                                    ->formatStateUsing(fn(string $state): string => $state === 'male' ? 'Laki-laki' : 'Perempuan'),
                                Infolists\Components\TextEntry::make('place_birth')
                                    ->label('Tempat Lahir'),
                                Infolists\Components\TextEntry::make('date_birth')
                                    ->label('Tanggal Lahir')
                                    ->date('d F Y'),
                                Infolists\Components\TextEntry::make('marital_status')
                                    ->label('Status Perkawinan'),
                            ])->columnSpan(1),

                        Infolists\Components\Section::make('Kontak & Alamat')
                            ->schema([
                                Infolists\Components\TextEntry::make('email')
                                    ->label('Email Pribadi')
                                    ->icon('heroicon-m-envelope'),
                                Infolists\Components\TextEntry::make('office_email')
                                    ->label('Email Kantor')
                                    ->icon('heroicon-m-briefcase')
                                    ->color('primary')
                                    ->weight('bold')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('phone_number')
                                    ->label('No. Telepon')
                                    ->icon('heroicon-m-phone'),
                                Infolists\Components\TextEntry::make('address')
                                    ->label('Alamat')
                                    ->columnSpanFull(),
                            ])->columnSpan(1),

                        Infolists\Components\Section::make('Kepegawaian & Finansial')
                            ->schema([
                                Infolists\Components\TextEntry::make('department.name')
                                    ->label('Departemen'),
                                Infolists\Components\TextEntry::make('education.name')
                                    ->label('Pendidikan Terakhir')
                                    ->badge()
                                    ->color('info'),
                                Infolists\Components\TextEntry::make('entry_date')
                                    ->label('Tanggal Masuk')
                                    ->date('d M Y'),
                                Infolists\Components\TextEntry::make('grade.name')
                                    ->label('Golongan')
                                    ->badge()
                                    ->color('success'),
                                Infolists\Components\TextEntry::make('serviceGrade.service_grade')
                                    ->label('Masa Kerja Golongan (MKG)')
                                    ->suffix(' Tahun'),
                                Infolists\Components\TextEntry::make('basic_salary_amount')
                                    ->label('Gaji Pokok')
                                    ->money('IDR')
                                    ->weight('bold')
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('bank_account_number')
                                    ->label('No. Rekening Bank'),
                                Infolists\Components\TextEntry::make('npwp_number')
                                    ->label('NPWP'),
                            ])->columnSpan(1),
                    ]),

                Infolists\Components\ViewEntry::make('recruitment_progress')
                    ->view('filament.components.recruitment-progress-bar')
                    ->columnSpanFull()
                    ->hiddenLabel(),

                Infolists\Components\ViewEntry::make('family_data')
                    ->view('filament.components.family-data-table')
                    ->columnSpanFull()
                    ->hiddenLabel()
                    ->hidden(fn (Employee $record) => $record->families->isEmpty()),

                Infolists\Components\ViewEntry::make('agreement_data')
                    ->view('filament.components.agreement-data-table')
                    ->columnSpanFull()
                    ->hiddenLabel()
                    ->hidden(fn (Employee $record) => $record->employeeAgreements->isEmpty()),

                Infolists\Components\ViewEntry::make('mutation_data')
                    ->view('filament.components.mutation-data-table')
                    ->columnSpanFull()
                    ->hiddenLabel()
                    ->hidden(fn (Employee $record) => $record->mutations->isEmpty()),

                Infolists\Components\ViewEntry::make('career_movement_data')
                    ->view('filament.components.career-movement-table')
                    ->columnSpanFull()
                    ->hiddenLabel()
                    ->hidden(fn (Employee $record) => $record->careerMovements->isEmpty()),

                Infolists\Components\ViewEntry::make('grade_promotion_data')
                    ->view('filament.components.promotion-history-table')
                    ->columnSpanFull()
                    ->hiddenLabel()
                    ->hidden(fn (Employee $record) => $record->promotions->isEmpty()),

                Infolists\Components\ViewEntry::make('appointment_data')
                    ->view('filament.components.appointment-history-table')
                    ->columnSpanFull()
                    ->hiddenLabel()
                    ->hidden(fn (Employee $record) => $record->appointments->isEmpty()),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            EmployeeResource\Widgets\EmployeeStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
