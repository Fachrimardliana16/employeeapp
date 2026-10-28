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
use App\Models\MasterEmployeeServiceGrade;
use App\Models\MasterSubDepartment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\DeleteAction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Pegawai';
    protected static ?string $modelLabel = 'Pegawai';
    protected static ?string $pluralModelLabel = 'Pegawai';
    protected static ?string $navigationGroup = 'Manajemen Pegawai';
    protected static ?int $navigationSort = 201;

    /**
     * Per-request in-memory cache for reference table lookups.
     * Eliminates 9 repeated DB round-trips on each employee list load.
     * Populated via Cache::remember (1-hour TTL) on first access per request.
     */
    private static ?array $_lookupCache = null;

    /**
     * Return all reference lookup data from cache (1 DB query vs 9 eager-load queries).
     * Keys: positions, statuses, grades, serviceGrades, departments, deptTypes, subDepartments
     */
    public static function getLookupData(): array
    {
        if (self::$_lookupCache !== null) {
            return self::$_lookupCache;
        }

        self::$_lookupCache = Cache::remember('employee_list_lookups', now()->addHour(), function () {
            return [
                'positions'      => MasterEmployeePosition::pluck('name', 'id')->toArray(),
                'statuses'       => MasterEmployeeStatusEmployment::pluck('name', 'id')->toArray(),
                'grades'         => MasterEmployeeGrade::pluck('name', 'id')->toArray(),
                'serviceGrades'  => MasterEmployeeServiceGrade::pluck('service_grade', 'id')->toArray(),
                'departments'    => MasterDepartment::pluck('name', 'id')->toArray(),
                'deptTypes'      => MasterDepartment::pluck('type', 'id')->toArray(),
                'subDepartments' => MasterSubDepartment::pluck('name', 'id')->toArray(),
            ];
        });

        return self::$_lookupCache;
    }

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
                                                    ->imageResizeMode('cover')
                                                    ->imageResizeTargetWidth(800)
                                                    ->imageResizeTargetHeight(800)
                                                    ->disk('public')
                                                    ->visibility('public')
                                                    ->directory('employees/photos')
                                                    ->maxSize(15360)
                                                    ->imageEditor()
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                                                    ->optimize('webp')
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
                                                        Forms\Components\TextInput::make('pin')
                                                            ->label('PIN Absensi')
                                                            ->unique(ignoreRecord: true)
                                                            ->helperText('ID yang digunakan pada mesin absensi')
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
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y')
                                                    ->format('Y-m-d')
                                                    ->placeholder('Tgl/Bln/Thn (Contoh: 17/08/1945)')
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
                                                Forms\Components\Select::make('bpjs_tk_status')
                                                    ->label('Status BPJS Ketenagakerjaan')
                                                    ->options([
                                                        'Aktif' => 'Aktif',
                                                        'Non-Aktif' => 'Non-Aktif',
                                                    ])
                                                    ->placeholder('Pilih status'),
                                                Forms\Components\TextInput::make('bpjs_kes_number')
                                                    ->label('Nomor BPJS Kesehatan')
                                                    ->numeric()
                                                    ->rules(['regex:/^[0-9]+$/'])
                                                    ->placeholder('0001234567890')
                                                    ->helperText('13 digit nomor BPJS Kesehatan')
                                                    ->maxLength(13),
                                                Forms\Components\Select::make('bpjs_kes_status')
                                                    ->label('Status BPJS Kesehatan')
                                                    ->options([
                                                        'Aktif' => 'Aktif',
                                                        'Non-Aktif' => 'Non-Aktif',
                                                    ])
                                                    ->placeholder('Pilih status'),
                                                Forms\Components\Select::make('bpjs_kes_class')
                                                    ->label('Kelas Layanan BPJS')
                                                    ->options([
                                                        'Kelas 1' => 'Kelas 1',
                                                        'Kelas 2' => 'Kelas 2',
                                                        'Kelas 3' => 'Kelas 3',
                                                    ])
                                                    ->placeholder('Pilih kelas'),
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
                                                Forms\Components\TextInput::make('dapenma_number')
                                                    ->label('Nomor Dapenma')
                                                    ->placeholder('Masukkan nomor Dapenma')
                                                    ->maxLength(50),
                                                Forms\Components\TextInput::make('dapenma_phdp')
                                                    ->label('PHDP Dapenma')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->placeholder('0'),
                                                Forms\Components\Select::make('dapenma_status')
                                                    ->label('Status Dapenma')
                                                    ->options([
                                                        'Aktif' => 'Aktif',
                                                        'Non-Aktif' => 'Non-Aktif',
                                                    ])
                                                    ->placeholder('Pilih status'),
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
                                                    ->visible(fn(Forms\Get $get) => $get('work_unit_type') === 'bagian')
                                                    ->live()
                                                    ->afterStateUpdated(fn($state, Forms\Set $set) => $set('departments_id', $state)),

                                                Forms\Components\Select::make('cabang_id')
                                                    ->label('Pilih Cabang')
                                                    ->options(\App\Models\MasterDepartment::cabang()->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->visible(fn(Forms\Get $get) => $get('work_unit_type') === 'cabang')
                                                    ->live()
                                                    ->afterStateUpdated(fn($state, Forms\Set $set) => $set('departments_id', $state)),

                                                Forms\Components\Select::make('unit_id')
                                                    ->label('Pilih Unit')
                                                    ->options(\App\Models\MasterDepartment::unit()->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->visible(fn(Forms\Get $get) => $get('work_unit_type') === 'unit')
                                                    ->live()
                                                    ->afterStateUpdated(fn($state, Forms\Set $set) => $set('departments_id', $state)),

                                                Forms\Components\Select::make('sub_department_id')
                                                    ->label('Sub Bagian')
                                                    ->options(function (Forms\Get $get) {
                                                        $deptId = $get('departments_id');
                                                        if (!$deptId) return [];
                                                        return \App\Models\MasterSubDepartment::where('departments_id', $deptId)->pluck('name', 'id');
                                                    })
                                                    ->searchable()
                                                    ->preload()
                                                    ->visible(fn(Forms\Get $get) => $get('departments_id') !== null),

                                                Forms\Components\Hidden::make('departments_id'),
                                                Forms\Components\Select::make('employee_position_id')
                                                    ->label('Posisi/Jabatan')
                                                    ->relationship('position', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->live(),
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

                                                Forms\Components\Section::make('Estimasi Pendapatan & Potongan (Simulasi)')
                                                    ->description('Berdasarkan Jabatan, Golongan, dan Aturan Global')
                                                    ->icon('heroicon-o-banknotes')
                                                    ->visible(fn(Forms\Get $get) => $get('employee_position_id') || $get('basic_salary_id'))
                                                    ->schema([
                                                        Forms\Components\Grid::make(2)
                                                            ->schema([
                                                                Forms\Components\Group::make([
                                                                    Forms\Components\Placeholder::make('global_benefits_info')
                                                                        ->label('Tunjangan Global (Otomatis)')
                                                                        ->content('Keluarga (10%), BPJS Kes (4%), JHT (3.7%), Beras, Air. (Dihitung otomatis oleh sistem)'),

                                                                    Forms\Components\Placeholder::make('position_benefits_summary')
                                                                        ->label('Tunjangan Jabatan (Fixed)')
                                                                        ->content(function (Forms\Get $get) {
                                                                            $posId = $get('employee_position_id');
                                                                            if (!$posId) return '-';
                                                                            $benefits = \App\Models\MasterEmployeePositionBenefit::where('employee_position_id', $posId)->get();
                                                                            if ($benefits->isEmpty()) return 'Tidak ada tunjangan jabatan khusus';

                                                                            $list = $benefits->map(fn($b) => ($b->benefit->name ?? 'Tunjangan') . ': Rp ' . number_format($b->amount, 0, ',', '.'))->join('<br>');
                                                                            return new \Illuminate\Support\HtmlString($list);
                                                                        }),

                                                                    Forms\Components\Placeholder::make('grade_benefits_summary')
                                                                        ->label('Tunjangan Golongan')
                                                                        ->content(function (Forms\Get $get) {
                                                                            $gradeId = $get('basic_salary_id');
                                                                            if (!$gradeId) return '-';
                                                                            $benefits = \App\Models\MasterEmployeeGradeBenefit::where('employee_grade_id', $gradeId)->get();
                                                                            if ($benefits->isEmpty()) return 'Tidak ada tunjangan golongan khusus';

                                                                            $list = $benefits->map(fn($b) => ($b->benefit->name ?? 'Tunjangan') . ': Rp ' . number_format($b->amount, 0, ',', '.'))->join('<br>');
                                                                            return new \Illuminate\Support\HtmlString($list);
                                                                        }),
                                                                ]),

                                                                Forms\Components\Group::make([
                                                                    Forms\Components\Placeholder::make('position_cuts_summary')
                                                                        ->label('Potongan Jabatan (Mandatori)')
                                                                        ->content(function (Forms\Get $get) {
                                                                            $posId = $get('employee_position_id');
                                                                            if (!$posId) return '-';
                                                                            $cuts = \App\Models\MasterEmployeePositionSalaryCut::where('employee_position_id', $posId)->get();
                                                                            if ($cuts->isEmpty()) return 'Tidak ada potongan jabatan khusus';

                                                                            $list = $cuts->map(fn($c) => ($c->salaryCut->name ?? 'Potongan') . ': Rp ' . number_format($c->amount, 0, ',', '.'))->join('<br>');
                                                                            return new \Illuminate\Support\HtmlString($list);
                                                                        }),

                                                                    Forms\Components\Placeholder::make('total_estimation_gross')
                                                                        ->label('Estimasi Bruto Terhitung')
                                                                        ->content(function (Forms\Get $get) {
                                                                            $posId = $get('employee_position_id');
                                                                            $gradeId = $get('basic_salary_id');
                                                                            $mkgId = $get('employee_service_grade_id');

                                                                            $base = 0;
                                                                            if ($gradeId && $mkgId) {
                                                                                $base = \App\Models\MasterEmployeeBasicSalary::where('employee_grade_id', $gradeId)
                                                                                    ->where('employee_service_grade_id', $mkgId)
                                                                                    ->value('amount') ?? 0;
                                                                            }

                                                                            $posSum = $posId ? \App\Models\MasterEmployeePositionBenefit::where('employee_position_id', $posId)->sum('amount') : 0;
                                                                            $gradeSum = $gradeId ? \App\Models\MasterEmployeeGradeBenefit::where('employee_grade_id', $gradeId)->sum('amount') : 0;

                                                                            // Also add family (approx 10%)
                                                                            $family = $base * 0.1;
                                                                            $beras = 150000;
                                                                            $air = 101100;

                                                                            return 'Rp ' . number_format($base + $posSum + $gradeSum + $family + $beras + $air, 0, ',', '.') . ' (Termasuk estimasi global)';
                                                                        })->extraAttributes(['class' => 'font-bold text-primary-600']),
                                                                ]),
                                                            ]),
                                                    ])->columnSpanFull(),
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
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y')
                                                    ->format('Y-m-d')
                                                    ->placeholder('Tgl/Bln/Thn')
                                                    ->helperText('Tanggal mulai bekerja'),
                                                Forms\Components\DatePicker::make('probation_appointment_date')
                                                    ->label('Tanggal Calon Pegawai')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y')
                                                    ->format('Y-m-d')
                                                    ->placeholder('Tgl/Bln/Thn')
                                                    ->after('entry_date')
                                                    ->helperText('Tanggal pengangkatan menjadi Calon Pegawai (CP)'),
                                                Forms\Components\DatePicker::make('permanent_appointment_date')
                                                    ->label('Tanggal Pengangkatan Tetap')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y')
                                                    ->format('Y-m-d')
                                                    ->placeholder('Tgl/Bln/Thn')
                                                    ->after('probation_appointment_date')
                                                    ->helperText('Tanggal pengangkatan menjadi Pegawai Tetap. Masa kerja dihitung dari sini.'),
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
                Tables\Columns\TextColumn::make('image')
                    ->label('Foto Pas')
                    ->formatStateUsing(fn($state) => $state ? '<div class="flex justify-center"><img src="' . url('image-view/' . $state) . '" class="w-10 h-10 rounded-full object-cover shadow-sm border border-gray-200"></div>' : '-')
                    ->html()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('nippam')
                    ->label('NIPPAM / PIN')
                    ->description(fn(Employee $record): string => $record->pin ? "PIN: {$record->pin}" : "PIN: -")
                    ->searchable(['nippam', 'pin']),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->color(function (Employee $record) {
                        return ($record->next_kgb_date && $record->next_kgb_date <= now()) ||
                            ($record->next_promotion_date && $record->next_promotion_date <= now())
                            ? 'warning' : null;
                    }),
                Tables\Columns\TextColumn::make('id_number')
                    ->label('NIK (KTP)')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('unit_kerja')
                    ->label('Unit Kerja / Bagian')
                    ->getStateUsing(function (Employee $record) {
                        $lu = self::getLookupData();
                        $depts = $lu['departments'];
                        $subs  = $lu['subDepartments'];
                        $unitName = $depts[$record->unit_id]
                            ?? $depts[$record->cabang_id]
                            ?? $depts[$record->bagian_id]
                            ?? $depts[$record->departments_id]
                            ?? '-';
                        $subUnit = $subs[$record->sub_department_id] ?? '';
                        return $unitName . ($subUnit ? ' / ' . $subUnit : '');
                    })
                    ->description(function (Employee $record) {
                        $types = self::getLookupData()['deptTypes'];
                        return $types[$record->unit_id]
                            ?? $types[$record->cabang_id]
                            ?? $types[$record->bagian_id]
                            ?? $types[$record->departments_id]
                            ?? '';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->orWhereHas('department', fn($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('bagian', fn($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('cabang', fn($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('unit', fn($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Golongan')
                    ->getStateUsing(fn(Employee $record) => self::getLookupData()['grades'][$record->basic_salary_id] ?? '-')
                    ->sortable(query: fn(Builder $query, string $direction): Builder => $query->orderBy(
                        \App\Models\MasterEmployeeGrade::select('name')
                            ->whereColumn('id', 'employees.basic_salary_id')
                            ->limit(1),
                        $direction
                    ))
                    ->searchable(query: fn(Builder $query, string $search): Builder =>
                        $query->whereIn('basic_salary_id',
                            \App\Models\MasterEmployeeGrade::where('name', 'like', "%{$search}%")->pluck('id')
                        )
                    ),
                Tables\Columns\TextColumn::make('serviceGrade.service_grade')
                    ->label('MKG (Thn)')
                    ->getStateUsing(fn(Employee $record) => self::getLookupData()['serviceGrades'][$record->employee_service_grade_id] ?? '-')
                    ->sortable(query: fn(Builder $query, string $direction): Builder => $query->orderBy(
                        \App\Models\MasterEmployeeServiceGrade::select('service_grade')
                            ->whereColumn('id', 'employees.employee_service_grade_id')
                            ->limit(1),
                        $direction
                    ))
                    ->searchable(query: fn(Builder $query, string $search): Builder =>
                        $query->whereIn('employee_service_grade_id',
                            \App\Models\MasterEmployeeServiceGrade::where('service_grade', 'like', "%{$search}%")->pluck('id')
                        )
                    ),
                Tables\Columns\TextColumn::make('next_kgb_date')
                    ->label('KGB Berikutnya')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('next_promotion_date')
                    ->label('Golongan Berikutnya')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('basic_salary_amount')
                    ->label('Gaji Pokok')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('age')
                    ->label('Umur')
                    ->suffix(' Thn')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('retirement')
                    ->label('Pensiun')
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('position_status')
                    ->label('Posisi / Status')
                    ->getStateUsing(function (Employee $record) {
                        $lu = self::getLookupData();
                        $position = $lu['positions'][$record->employee_position_id] ?? '-';
                        $status   = $lu['statuses'][$record->employment_status_id] ?? '-';
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
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->orWhereHas('position', fn($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('employmentStatus', fn($q) => $q->where('name', 'like', "%{$search}%"));
                    }),
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
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('formatted_length_service')
                    ->label('Masa Kerja')
                    ->getStateUsing(fn(Employee $record) => $record->formatted_length_service)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('permanent_appointment_date', $direction === 'asc' ? 'desc' : 'asc');
                    })
                    ->icon('heroicon-m-clock')
                    ->placeholder('Belum ada data')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Tanggal Masuk')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('data_completeness')
                    ->label('Kelengkapan Data')
                    ->badge()
                    ->getStateUsing(fn(Employee $record) => $record->getDataCompletenessPercentage() . '%')
                    ->color(fn($state): string => match (true) {
                        ((int) $state) >= 90 => 'success',
                        ((int) $state) >= 70 => 'warning',
                        default => 'danger',
                    })
                    ->tooltip(
                        fn(Employee $record) => $record->hasIncompleteData()
                            ? 'Data yang belum lengkap: ' . implode(', ', $record->getMissingDataFields())
                            : 'Data sudah lengkap'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
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
                                            ->label('Pilih Bagian')
                                            ->options(fn() => \App\Models\MasterDepartment::pluck('name', 'id')->toArray())
                                            ->searchable()
                                            ->placeholder('Semua Departemen'),
                                        Forms\Components\Select::make('position_filter')
                                            ->label('Posisi/Jabatan')
                                            ->options(fn() => \App\Models\MasterEmployeePosition::pluck('name', 'id')->toArray())
                                            ->searchable()
                                            ->placeholder('Semua Posisi'),
                                        Forms\Components\Select::make('status_filter')
                                            ->label('Status Kepegawaian')
                                            ->options(fn() => \App\Models\MasterEmployeeStatusEmployment::pluck('name', 'id')->toArray())
                                            ->searchable()
                                            ->placeholder('Semua Status'),
                                        Forms\Components\Select::make('education_filter')
                                            ->label('Tingkat Pendidikan')
                                            ->options(fn() => \App\Models\MasterEmployeeEducation::pluck('name', 'id')->toArray())
                                            ->searchable()
                                            ->placeholder('Semua Tingkat Pendidikan'),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data, $livewire) {
                        return static::generateFilteredPDFReport(
                            $data['department_filter'] ?? null,
                            $data['position_filter'] ?? null,
                            $data['status_filter'] ?? null,
                            $data['education_filter'] ?? null
                        );
                    })
                    ->tooltip('Generate laporan Pegawai dalam format PDF dengan filter'),

                Tables\Actions\Action::make('generate_schedule')
                    ->label('Cetak Jadwal Kenaikan')
                    ->icon('heroicon-o-calendar-days')
                    ->color('info')
                    ->modalHeading('Cetak Jadwal Tahunan (KGB & Golongan)')
                    ->modalDescription('Pilih tahun dan pejabat berwenang untuk mencetak jadwal pengingat.')
                    ->form([
                        Forms\Components\Select::make('year')
                            ->label('Pilih Tahun')
                            ->options(function () {
                                $years = [];
                                $currentYear = now()->year;
                                for ($i = $currentYear; $i <= $currentYear + 5; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            })
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire) {
                        $url = route('report.career-schedule', [
                            'year' => $data['year'],
                        ]);
                        $livewire->js("window.open('{$url}', '_blank')");
                    })
                    ->tooltip('Cetak pengingat jadwal kenaikan berkala dan golongan untuk tahun depan'),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('download_template')
                        ->label('Download Template Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(fn() => static::downloadEmployeeImportTemplateExcel())
                        ->tooltip('Template Excel dengan tab Data Import, Referensi, dan Contoh Pengisian'),

                    Tables\Actions\Action::make('download_template_csv')
                        ->label('Download Template CSV')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('gray')
                        ->action(fn() => static::downloadEmployeeImportTemplateCsv())
                        ->tooltip('Template CSV untuk kebutuhan import format CSV'),

                    Tables\Actions\Action::make('import_pegawai')
                        ->label('Import / Sync Data')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('success')
                        ->modalSubmitActionLabel('Generate Preview')
                        ->modalWidth('2xl')
                        ->form(function () {
                            // Dynamically fetch options for help text (cached 1 jam)
                            $grades = \Illuminate\Support\Facades\Cache::remember('import_grades_hint', now()->addHour(), fn() =>
                                \App\Models\MasterEmployeeGrade::all()->map(fn($g) => "{$g->id}:{$g->name}")->implode(', ')
                            );
                            $edus = \Illuminate\Support\Facades\Cache::remember('import_edus_hint', now()->addHour(), fn() =>
                                \App\Models\MasterEmployeeEducation::all()->map(fn($e) => "{$e->id}:{$e->name}")->implode(', ')
                            );

                            return [
                                Forms\Components\FileUpload::make('import_file')
                                    ->label('File Pegawai (CSV / XLS / XLSX)')
                                    ->helperText(new \Illuminate\Support\HtmlString("
                                        <strong>Panduan Singkat:</strong><br>
                                        - Untuk EDIT: Isi kolom <code>id</code>. Untuk BARU: Kosongkan <code>id</code>.<br>
                                        - Gunakan tab <strong>Data Import</strong> dari Template Excel saat mengisi data.<br>
                                        - Tab <strong>Referensi</strong> dan <strong>Contoh Pengisian</strong> disediakan agar tidak salah format.<br>
                                        - Data Master (Pendidikan, Jabatan, dll) bisa diisi <strong>Nama</strong> atau <strong>Angka ID</strong>.<br>
                                        - Contoh ID Golongan: <code>{$grades}</code><br>
                                        - Contoh ID Pendidikan: <code>{$edus}</code><br>
                                        - Tanggal wajib format <code>dd-mm-yyyy</code> (contoh: 31-01-1990).<br>
                                        - Jika kolom <code>pin</code> kosong, sistem akan generate otomatis dalam rentang <strong>1-999</strong>.<br>
                                        - Import tahap ini hanya menampilkan <strong>Preview</strong>; setelah preview muncul, klik <strong>Submit Import</strong> di popup.
                                    "))
                                    ->required()
                                    ->acceptedFileTypes([
                                        'text/csv',
                                        'application/csv',
                                        'text/plain',
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    ]),
                            ];
                        })
                        ->action(function (array $data, $livewire) {
                            set_time_limit(300); // 5 minutes
                            ini_set('memory_limit', '512M');

                            $disk = \Illuminate\Support\Facades\Storage::disk('public');
                            $storedPath = $data['import_file'];
                            $path = $disk->path($storedPath);

                            try {
                                try {
                                    $tempRows = static::readEmployeeImportRows($path);
                                } catch (\Throwable $e) {
                                    Notification::make()
                                        ->title('File Tidak Valid')
                                        ->body($e->getMessage())
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                if (empty($tempRows)) {
                                    Notification::make()
                                        ->title('File Tidak Valid')
                                        ->body('File tidak memiliki data untuk diproses.')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                // 1. Find the Header Row (Look for 'id', 'nippam', 'name')
                                $headerIndex = -1;
                                foreach ($tempRows as $idx => $row) {
                                    // Strip BOM and non-breaking spaces
                                    $rowNormalized = array_map(function ($v) {
                                        $v = str_replace("\xEF\xBB\xBF", '', (string) $v);
                                        return strtolower(trim($v));
                                    }, $row);

                                    if (in_array('id', $rowNormalized, true) || in_array('nippam', $rowNormalized, true) || in_array('name', $rowNormalized, true)) {
                                        $headerIndex = $idx;
                                        $headers = $rowNormalized;
                                        break;
                                    }
                                }

                                if ($headerIndex === -1) {
                                    Notification::make()->title('File Tidak Valid')->body('Baris header (id, nippam, name) tidak ditemukan.')->danger()->send();
                                    return;
                                }

                                $dataRows = array_slice($tempRows, $headerIndex + 1);
                                $instructionTemplateByHeader = static::buildEmployeeImportInstructionMap($headers);
                                $headerCount = count($headers);

                                // Pre-load necessary masters for performance
                                $masterLookups = Cache::remember('employee_import_master_lookups', now()->addMinutes(15), function () {
                                    return [
                                        'units' => \App\Models\MasterDepartment::query()->pluck('id', 'name')
                                            ->mapWithKeys(fn($id, $name) => [strtolower(trim((string) $name)) => (int) $id])
                                            ->toArray(),
                                        'sub_depts' => \App\Models\MasterSubDepartment::query()->pluck('id', 'name')
                                            ->mapWithKeys(fn($id, $name) => [strtolower(trim((string) $name)) => (int) $id])
                                            ->toArray(),
                                        'positions' => \App\Models\MasterEmployeePosition::query()->pluck('id', 'name')
                                            ->mapWithKeys(fn($id, $name) => [strtolower(trim((string) $name)) => (int) $id])
                                            ->toArray(),
                                        'status' => \App\Models\MasterEmployeeStatusEmployment::query()->pluck('id', 'name')
                                            ->mapWithKeys(fn($id, $name) => [strtolower(trim((string) $name)) => (int) $id])
                                            ->toArray(),
                                        'education' => \App\Models\MasterEmployeeEducation::query()->pluck('id', 'name')
                                            ->mapWithKeys(fn($id, $name) => [strtolower(trim((string) $name)) => (int) $id])
                                            ->toArray(),
                                        'grades' => \App\Models\MasterEmployeeGrade::query()->pluck('id', 'name')
                                            ->mapWithKeys(fn($id, $name) => [strtolower(trim((string) $name)) => (int) $id])
                                            ->toArray(),
                                        'mkg' => \App\Models\MasterEmployeeServiceGrade::query()
                                            ->pluck('id', 'service_grade')
                                            ->mapWithKeys(fn($id, $serviceGrade) => [(string) $serviceGrade => (int) $id])
                                            ->toArray(),
                                        'agreements' => \App\Models\MasterEmployeeAgreement::query()->pluck('id', 'name')
                                            ->mapWithKeys(fn($id, $name) => [strtolower(trim((string) $name)) => (int) $id])
                                            ->toArray(),
                                    ];
                                });

                                $mkgById = Cache::remember('employee_import_mkg_by_id', now()->addMinutes(15), function () {
                                    return \App\Models\MasterEmployeeServiceGrade::query()
                                        ->pluck('id')
                                        ->flip()
                                        ->toArray();
                                });

                                $normalizeUniqueValue = function ($value, string $column): ?string {
                                    $normalizedValue = trim((string) $value);
                                    if ($normalizedValue === '') {
                                        return null;
                                    }

                                    if (in_array($column, ['email', 'office_email', 'username'], true)) {
                                        return strtolower($normalizedValue);
                                    }

                                    return $normalizedValue;
                                };

                                $uniqueLabels = [
                                    'pin' => 'PIN Absensi',
                                    'nippam' => 'NIPPAM',
                                    'email' => 'Email',
                                    'office_email' => 'Email Kantor',
                                    'username' => 'Username',
                                    'id_number' => 'NIK (KTP)',
                                ];

                                $uniques = [
                                    'pin' => [],
                                    'nippam' => [],
                                    'email' => [],
                                    'office_email' => [],
                                    'username' => [],
                                ];
                                $activeEmployeeIdByNippam = [];
                                $employeePinsById = [];

                                $employeeReferenceRows = \App\Models\Employee::withTrashed()
                                    ->get(['id', 'name', 'pin', 'nippam', 'email', 'office_email', 'username', 'deleted_at']);

                                foreach ($employeeReferenceRows as $employee) {
                                    $isDeleted = $employee->deleted_at !== null;
                                    $employeeId = (int) $employee->id;

                                    $pinValue = $normalizeUniqueValue($employee->pin, 'pin');
                                    $employeePinsById[$employeeId] = $pinValue;
                                    if ($pinValue !== null) {
                                        $uniques['pin'][$pinValue] = [
                                            'id' => $employeeId,
                                            'name' => $employee->name,
                                            'is_deleted' => $isDeleted,
                                        ];
                                    }

                                    $nippamValue = $normalizeUniqueValue($employee->nippam, 'nippam');
                                    if ($nippamValue !== null) {
                                        $uniques['nippam'][$nippamValue] = [
                                            'id' => $employeeId,
                                            'name' => $employee->name,
                                            'is_deleted' => $isDeleted,
                                        ];

                                        if (!$isDeleted) {
                                            $activeEmployeeIdByNippam[$nippamValue] = $employeeId;
                                        }
                                    }

                                    $emailValue = $normalizeUniqueValue($employee->email, 'email');
                                    if ($emailValue !== null) {
                                        $uniques['email'][$emailValue] = [
                                            'id' => $employeeId,
                                            'name' => $employee->name,
                                            'is_deleted' => $isDeleted,
                                        ];
                                    }

                                    $officeEmailValue = $normalizeUniqueValue($employee->office_email, 'office_email');
                                    if ($officeEmailValue !== null) {
                                        $uniques['office_email'][$officeEmailValue] = [
                                            'id' => $employeeId,
                                            'name' => $employee->name,
                                            'is_deleted' => $isDeleted,
                                        ];
                                    }

                                    $usernameValue = $normalizeUniqueValue($employee->username, 'username');
                                    if ($usernameValue !== null) {
                                        $uniques['username'][$usernameValue] = [
                                            'id' => $employeeId,
                                            'name' => $employee->name,
                                            'is_deleted' => $isDeleted,
                                        ];
                                    }
                                }

                                $fileProvidedPins = [];
                                $normalizedImportRows = [];

                                foreach ($dataRows as $idx => $row) {
                                    $actualRowInFile = $headerIndex + $idx + 2;

                                    if (empty($row)) {
                                        continue;
                                    }

                                    $rowWithKnownHeaders = array_slice($row, 0, $headerCount);
                                    $rowData = array_combine($headers, array_pad($rowWithKnownHeaders, $headerCount, ''));
                                    foreach ($rowData as $k => $v) {
                                        $rowData[$k] = trim((string) $v);
                                    }

                                    if (!array_filter($rowData, fn($value) => $value !== '')) {
                                        continue;
                                    }

                                    if (static::isEmployeeImportInstructionRow($rowData, $instructionTemplateByHeader)) {
                                        continue;
                                    }

                                    $normalizedImportRows[] = [
                                        'line_number' => $actualRowInFile,
                                        'row' => $rowData,
                                    ];

                                    $providedPin = $normalizeUniqueValue($rowData['pin'] ?? '', 'pin');
                                    if ($providedPin !== null) {
                                        $fileProvidedPins[$providedPin] = true;
                                    }
                                }

                                $reservedNumericPins = [];

                                foreach (array_keys($uniques['pin']) as $existingPin) {
                                    if (!ctype_digit((string) $existingPin)) {
                                        continue;
                                    }

                                    $numericPin = (int) $existingPin;
                                    if ($numericPin < 1 || $numericPin > 999) {
                                        continue;
                                    }
                                    $reservedNumericPins[$numericPin] = true;
                                }

                                foreach (array_keys($fileProvidedPins) as $providedPin) {
                                    if (!ctype_digit((string) $providedPin)) {
                                        continue;
                                    }

                                    $numericPin = (int) $providedPin;
                                    if ($numericPin < 1 || $numericPin > 999) {
                                        continue;
                                    }
                                    $reservedNumericPins[$numericPin] = true;
                                }

                                $nextAutoGeneratedPin = 1;
                                $generateAutoPin = function () use (&$reservedNumericPins, &$nextAutoGeneratedPin): ?string {
                                    while ($nextAutoGeneratedPin <= 999 && isset($reservedNumericPins[$nextAutoGeneratedPin])) {
                                        $nextAutoGeneratedPin++;
                                    }

                                    if ($nextAutoGeneratedPin > 999) {
                                        return null;
                                    }

                                    $generatedPin = (string) $nextAutoGeneratedPin;
                                    $reservedNumericPins[$nextAutoGeneratedPin] = true;
                                    $nextAutoGeneratedPin++;

                                    return $generatedPin;
                                };

                                $errors = [];
                                $warnings = [];
                                $processedData = [];
                                $batchUniques = [
                                    'pin' => [],
                                    'nippam' => [],
                                    'email' => [],
                                    'office_email' => [],
                                    'username' => [],
                                    'id_number' => [],
                                ];

                                // Phase 1: Validation
                                foreach ($normalizedImportRows as $importRow) {
                                    $actualRowInFile = $importRow['line_number'];
                                    $rowData = $importRow['row'];

                                    $rowErrors = [];
                                    if (empty($rowData['name'])) $rowErrors[] = "Nama wajib diisi";

                                    $resolvedEmployeeId = null;
                                    $rowIdValue = trim((string) ($rowData['id'] ?? ''));
                                    if ($rowIdValue !== '') {
                                        if (!ctype_digit($rowIdValue)) {
                                            $rowErrors[] = "ID '{$rowIdValue}' tidak valid (harus angka).";
                                        } else {
                                            $resolvedEmployeeId = (int) $rowIdValue;
                                        }
                                    } else {
                                        $nippamForFallbackUpdate = $normalizeUniqueValue($rowData['nippam'] ?? '', 'nippam');
                                        if ($nippamForFallbackUpdate !== null && isset($activeEmployeeIdByNippam[$nippamForFallbackUpdate])) {
                                            $resolvedEmployeeId = (int) $activeEmployeeIdByNippam[$nippamForFallbackUpdate];
                                            $rowData['id'] = (string) $resolvedEmployeeId;
                                        }
                                    }

                                    $isNewRow = empty($rowData['id']);
                                    $existingEmployeePin = $resolvedEmployeeId !== null
                                        ? ($employeePinsById[$resolvedEmployeeId] ?? null)
                                        : null;

                                    // Smart Lookup Helper
                                    $smartLookup = function ($key, $name) use ($masterLookups) {
                                        if (empty($name)) return null;
                                        $search = strtolower(trim($name));
                                        if (isset($masterLookups[$key][$search])) return $masterLookups[$key][$search];
                                        if (is_numeric($name)) return (int) $name;
                                        return null;
                                    };

                                    // Specialized Unit Lookup
                                    $unitName = $rowData['work_unit_name'] ?? '';
                                    $unitType = strtolower($rowData['work_unit_type'] ?? ''); // bagian/cabang/unit
                                    $deptId = $bagianId = $cabangId = $unitId = null;

                                    if (!empty($unitName)) {
                                        $unitKey = strtolower(trim($unitName));
                                        if (isset($masterLookups['units'][$unitKey])) {
                                            $deptId = $masterLookups['units'][$unitKey];
                                        } elseif (is_numeric($unitName)) {
                                            $deptId = (int) $unitName;
                                        }

                                        if (!$deptId) {
                                            $rowErrors[] = "Unit Kerja '{$unitName}' ({$unitType}) tidak ditemukan";
                                        } else {
                                            if ($unitType === 'bagian') $bagianId = $deptId;
                                            elseif ($unitType === 'cabang') $cabangId = $deptId;
                                            elseif ($unitType === 'unit') $unitId = $deptId;
                                        }
                                    }

                                    $subDeptId = $smartLookup('sub_depts', $rowData['sub_department_name'] ?? '');
                                    $posId = $smartLookup('positions', $rowData['position_name'] ?? '');
                                    $statusId = $smartLookup('status', $rowData['employment_status_name'] ?? '');
                                    $eduId = $smartLookup('education', $rowData['education_name'] ?? '');
                                    $gradeId = $smartLookup('grades', $rowData['grade_name'] ?? '');
                                    $agreementId = $smartLookup('agreements', $rowData['agreement_type_name'] ?? '');

                                    // Uniqueness Validation
                                    $checkUnique = function (string $column, $value, string $source = 'input') use (
                                        &$rowErrors,
                                        &$batchUniques,
                                        $uniques,
                                        $uniqueLabels,
                                        $normalizeUniqueValue,
                                        $resolvedEmployeeId,
                                        $actualRowInFile
                                    ) {
                                        $normalizedValue = $normalizeUniqueValue($value, $column);
                                        if ($normalizedValue === null) {
                                            return;
                                        }

                                        $label = $uniqueLabels[$column] ?? strtoupper($column);

                                        if (isset($uniques[$column][$normalizedValue])) {
                                            $existingRecord = $uniques[$column][$normalizedValue];
                                            if ((int) $existingRecord['id'] !== (int) ($resolvedEmployeeId ?? 0)) {
                                                $archivedSuffix = $existingRecord['is_deleted'] ? ' (arsip)' : '';
                                                $rowErrors[] = "{$label} '{$normalizedValue}' sudah digunakan oleh '{$existingRecord['name']}' (ID {$existingRecord['id']}){$archivedSuffix}";
                                                return;
                                            }
                                        }

                                        if (isset($batchUniques[$column][$normalizedValue])) {
                                            $previousEntry = $batchUniques[$column][$normalizedValue];
                                            if ((int) ($previousEntry['target_employee_id'] ?? 0) !== (int) ($resolvedEmployeeId ?? 0)) {
                                                $sourceSuffix = ($column === 'pin' && (($previousEntry['source'] ?? 'input') === 'generated' || $source === 'generated'))
                                                    ? ' (termasuk PIN otomatis)'
                                                    : '';
                                                $rowErrors[] = "{$label} '{$normalizedValue}' duplikat di file import (baris {$previousEntry['line']} dan {$actualRowInFile}){$sourceSuffix}";
                                                return;
                                            }
                                        }

                                        $batchUniques[$column][$normalizedValue] = [
                                            'line' => $actualRowInFile,
                                            'target_employee_id' => $resolvedEmployeeId,
                                            'source' => $source,
                                        ];
                                    };

                                    $providedPin = $normalizeUniqueValue($rowData['pin'] ?? '', 'pin');
                                    $effectivePin = $providedPin;
                                    $pinSource = 'input';

                                    if ($effectivePin === null) {
                                        if ($existingEmployeePin !== null) {
                                            $effectivePin = $existingEmployeePin;
                                            $pinSource = 'existing';
                                        } else {
                                            $effectivePin = $generateAutoPin();
                                            if ($effectivePin === null) {
                                                $rowErrors[] = 'PIN otomatis tidak tersedia. Rentang PIN 1-999 sudah penuh.';
                                            } else {
                                                $pinSource = 'generated';
                                            }
                                        }
                                    }

                                    if ($effectivePin !== null) {
                                        $checkUnique('pin', $effectivePin, $pinSource);
                                    }

                                    $checkUnique('nippam', $rowData['nippam'] ?? '');
                                    $checkUnique('email', $rowData['email'] ?? '');
                                    $checkUnique('office_email', $rowData['office_email'] ?? '');
                                    $checkUnique('username', $rowData['username'] ?? '');
                                    $checkUnique('id_number', $rowData['id_number'] ?? '');

                                    // MKG Lookup
                                    $mkgValue = trim($rowData['mkg_years'] ?? '');
                                    $mkgId = null;
                                    if ($mkgValue !== '') {
                                        if (isset($masterLookups['mkg'][(string) $mkgValue])) {
                                            $mkgId = $masterLookups['mkg'][$mkgValue];
                                        } elseif (is_numeric($mkgValue) && isset($mkgById[(int) $mkgValue])) {
                                            // Allow direct FK input if CSV/XLS/XLSX already provides relation ID.
                                            $mkgId = (int) $mkgValue;
                                        } else {
                                            $displayName = ($rowData['name'] ?? null) ?: ($rowData['nippam'] ?? 'N/A');
                                            $warnings[] = "Baris {$actualRowInFile} ({$displayName}): MKG '{$mkgValue}' tidak ditemukan, diset kosong.";
                                        }
                                    }

                                    if (!empty($rowErrors)) {
                                        $displayName = ($rowData['name'] ?? null) ?: ($rowData['nippam'] ?? 'N/A');
                                        $errors[] = "<strong>Baris {$actualRowInFile} ({$displayName}):</strong> " . implode(', ', $rowErrors);
                                    }

                                    $processedData[] = [
                                        'row' => $rowData,
                                        'line_number' => $actualRowInFile,
                                        'is_new' => $isNewRow,
                                        'effective_pin' => $effectivePin,
                                        'pin_source' => $pinSource,
                                        'ids' => [
                                            'dept_id' => $deptId,
                                            'bagian_id' => $bagianId,
                                            'cabang_id' => $cabangId,
                                            'unit_id' => $unitId,
                                            'sub_dept_id' => $subDeptId,
                                            'pos_id' => $posId,
                                            'status_id' => $statusId,
                                            'edu_id' => $eduId,
                                            'grade_id' => $gradeId,
                                            'mkg_id' => $mkgId,
                                            'agreement_id' => $agreementId,
                                        ]
                                    ];
                                }

                                if (!empty($errors)) {
                                    $totalErrors = count($errors);
                                    $errorDisplay = array_slice($errors, 0, 15);
                                    $footer = $totalErrors > 15 ? "<br><br><strong>...dan " . ($totalErrors - 15) . " baris lainnya juga bermasalah.</strong>" : "";

                                    Notification::make()
                                        ->title('Import Dibatalkan: Ditemukan ' . $totalErrors . ' Kesalahan Data')
                                        ->body(new \Illuminate\Support\HtmlString("Sistem menolak seluruh file agar data tetap aman. Mohon perbaiki sampel berikut:<br><br>" . implode('<br>', $errorDisplay) . $footer))
                                        ->danger()->persistent()->send();
                                    return;
                                }

                                $previewPayload = [
                                    'processed_data' => $processedData,
                                    'warnings' => $warnings,
                                    'generated_at' => now()->toIso8601String(),
                                    'generated_by' => auth()->id(),
                                ];

                                \Illuminate\Support\Facades\Cache::put(
                                    static::getEmployeeImportPreviewCacheKey(),
                                    $previewPayload,
                                    now()->addMinutes(60)
                                );

                                Notification::make()
                                    ->title('Preview Import Siap')
                                    ->body('Periksa seluruh data di popup preview, lalu klik Submit Import jika sudah sesuai.')
                                    ->success()
                                    ->persistent()
                                    ->send();

                                $livewire->replaceMountedTableAction('preview_import_pegawai');
                            } finally {
                                if ($disk->exists($storedPath)) {
                                    $disk->delete($storedPath);
                                }
                            }
                        }),
                    Tables\Actions\Action::make('preview_import_pegawai')
                        ->label('Preview & Submit Import')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->extraAttributes([
                            'style' => 'display: none !important;',
                        ])
                        ->modalHeading('Preview Import Pegawai')
                        ->modalDescription('Periksa data hasil import sebelum menyimpan ke database.')
                        ->modalWidth('7xl')
                        ->modalSubmitActionLabel('Submit Import')
                        ->modalCancelActionLabel('Tutup')
                        ->modalContent(fn() => view('filament.employee.partials.import-preview-content', static::getEmployeeImportPreviewViewData()))
                        ->action(function () {
                            $previewPayload = static::getEmployeeImportPreviewPayload();
                            $processedData = $previewPayload['processed_data'] ?? [];

                            if (empty($processedData)) {
                                Notification::make()
                                    ->title('Preview Tidak Ditemukan')
                                    ->body('Belum ada data preview. Jalankan Import / Sync Data terlebih dahulu.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $isSuccess = static::runEmployeeImportCommit(
                                $processedData,
                                $previewPayload['warnings'] ?? []
                            );

                            if ($isSuccess) {
                                Cache::forget(static::getEmployeeImportPreviewCacheKey());
                            }
                        }),
                ])
                    ->label('Manajemen Data')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->color('gray')
                    ->button(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('departments_id')
                    ->label('Bagian')
                    ->relationship('department', 'name')
                    ->label('Bagian')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('employee_position_id')
                    ->relationship('position', 'name')
                    ->label('Posisi/Jabatan')
                    ->searchable(),
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
                            ->orWhereNull('probation_appointment_date')
                            ->orWhereNull('pin');
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
                            ->whereNotNull('probation_appointment_date')
                            ->whereNotNull('pin');
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
                        ->url(fn(Employee $record): string => EmployeeResource::getUrl('edit', ['record' => $record]))
                        ->openUrlInNewTab(),
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
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Export Terpilih')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $headers = [
                                'id',
                                'nippam',
                                'pin',
                                'name',
                                'gender',
                                'place_birth',
                                'date_birth',
                                'religion',
                                'marital_status',
                                'blood_type',
                                'email',
                                'office_email',
                                'phone_number',
                                'id_number',
                                'familycard_number',
                                'npwp_number',
                                'bank_account_number',
                                'bpjs_tk_number',
                                'bpjs_tk_status',
                                'bpjs_kes_number',
                                'bpjs_kes_status',
                                'bpjs_kes_class',
                                'rek_dplk_pribadi',
                                'rek_dplk_bersama',
                                'dapenma_number',
                                'dapenma_phdp',
                                'dapenma_status',
                                'address',
                                'work_unit_type',
                                'work_unit_name',
                                'sub_department_name',
                                'position_name',
                                'employment_status_name',
                                'education_name',
                                'grade_name',
                                'mkg_years',
                                'agreement_type_name',
                                'entry_date',
                                'probation_appointment_date',
                                'leave_balance',
                                'agreement_date_start',
                                'agreement_date_end',
                                'grade_date_start',
                                'grade_date_end',
                                'periodic_salary_date_start',
                                'periodic_salary_date_end'
                            ];

                            $callback = function () use ($records, $headers) {
                                $file = fopen('php://output', 'w');
                                fputcsv($file, $headers);

                                // Add Instruction Row for consistency
                                fputcsv($file, [
                                    'KOSONGKAN untuk Pegawai Baru, ISI untuk Update',
                                    'Otomatis',
                                    'PIN Absensi',
                                    'Wajib',
                                    'male/female',
                                    'Wajib',
                                    'dd-mm-yyyy',
                                    'Islam/Kristen/sdh',
                                    'single/married/sdh',
                                    'A/B/O/AB',
                                    'Email Pribadi',
                                    'Email Kantor',
                                    'Hanya Angka',
                                    '16 Digit',
                                    '16 Digit',
                                    'NPWP',
                                    'No Rek',
                                    'No BPJS TK',
                                    'Aktif/Tidak',
                                    'No BPJS Kes',
                                    'Aktif/Tidak',
                                    'Kelas 1/2/3',
                                    'DPLK',
                                    'DPLK',
                                    'No Dapenma',
                                    'Hanya Angka',
                                    'Aktif/Tidak',
                                    'Alamat Lengkap',
                                    'bagian/cabang/unit',
                                    'Nama Bagian/ID',
                                    'Nama/ID',
                                    'Nama/ID',
                                    'Nama/ID',
                                    'Nama/ID',
                                    'Nama/ID',
                                    'Tahun (0/2/4)',
                                    'Nama/ID',
                                    'dd-mm-yyyy',
                                    'dd-mm-yyyy',
                                    'Angka',
                                    'dd-mm-yyyy',
                                    'dd-mm-yyyy',
                                    'dd-mm-yyyy',
                                    'dd-mm-yyyy',
                                    'dd-mm-yyyy',
                                    'dd-mm-yyyy'
                                ]);

                                foreach ($records as $record) {
                                    $record->load(['position', 'employmentStatus', 'education', 'grade', 'serviceGrade', 'agreement', 'department', 'subDepartment', 'bagian', 'cabang', 'unit']);

                                    // Determine work unit type and name
                                    $unitType = '';
                                    $unitName = '';
                                    if ($record->bagian_id) {
                                        $unitType = 'bagian';
                                        $unitName = $record->bagian->name ?? '';
                                    } elseif ($record->cabang_id) {
                                        $unitType = 'cabang';
                                        $unitName = $record->cabang->name ?? '';
                                    } elseif ($record->unit_id) {
                                        $unitType = 'unit';
                                        $unitName = $record->unit->name ?? '';
                                    } elseif ($record->departments_id) {
                                        $unitType = 'bagian';
                                        $unitName = $record->department->name ?? '';
                                    }

                                    fputcsv($file, [
                                        $record->id,
                                        $record->nippam,
                                        $record->pin,
                                        $record->name,
                                        $record->gender,
                                        $record->place_birth,
                                        $record->date_birth?->format('d-m-Y'),
                                        $record->religion,
                                        $record->marital_status,
                                        $record->blood_type,
                                        $record->email,
                                        $record->office_email,
                                        $record->phone_number,
                                        $record->id_number,
                                        $record->familycard_number,
                                        $record->npwp_number,
                                        $record->bank_account_number,
                                        $record->bpjs_tk_number,
                                        $record->bpjs_tk_status,
                                        $record->bpjs_kes_number,
                                        $record->bpjs_kes_status,
                                        $record->bpjs_kes_class,
                                        $record->rek_dplk_pribadi,
                                        $record->rek_dplk_bersama,
                                        $record->dapenma_number,
                                        $record->dapenma_phdp,
                                        $record->dapenma_status,
                                        $record->address,
                                        $unitType,
                                        $unitName,
                                        $record->subDepartment->name ?? $record->sub_department_id,
                                        $record->position->name ?? $record->employee_position_id,
                                        $record->employmentStatus->name ?? $record->employment_status_id,
                                        $record->education->name ?? $record->employee_education_id,
                                        $record->grade->name ?? $record->basic_salary_id,
                                        $record->serviceGrade->service_grade ?? $record->employee_service_grade_id,
                                        $record->agreement->name ?? $record->master_employee_agreement_id,
                                        $record->entry_date?->format('d-m-Y'),
                                        $record->probation_appointment_date?->format('d-m-Y'),
                                        $record->leave_balance,
                                        $record->agreement_date_start?->format('d-m-Y'),
                                        $record->agreement_date_end?->format('d-m-Y'),
                                        $record->grade_date_start?->format('d-m-Y'),
                                        $record->grade_date_end?->format('d-m-Y'),
                                        $record->periodic_salary_date_start?->format('d-m-Y'),
                                        $record->periodic_salary_date_end?->format('d-m-Y'),
                                    ]);
                                }
                                fclose($file);
                            };

                            return response()->streamDownload($callback, 'export_pegawai_' . now()->format('YmdHis') . '.csv');
                        }),
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50, 100]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make(3)
                    ->schema([
                        // Sidebar: Profil & Status Utama
                        Infolists\Components\Group::make([
                            Infolists\Components\Section::make('Profil Pegawai')
                                ->schema([
                                    Infolists\Components\ImageEntry::make('image')
                                        ->label('')
                                        ->circular()
                                        ->height(200)
                                        ->alignCenter()
                                        ->state(fn($record) => $record->image ? url('image-view/' . $record->image) : null)
                                        ->extraImgAttributes(['class' => 'shadow-lg border-2 border-primary-500'])
                                        ->placeholder('Tidak ada foto'),

                                    Infolists\Components\TextEntry::make('name')
                                        ->label('')
                                        ->weight('bold')
                                        ->size('lg')
                                        ->alignCenter(),

                                    Infolists\Components\TextEntry::make('nippam')
                                        ->label('')
                                        ->color('gray')
                                        ->size('sm')
                                        ->alignCenter()
                                        ->copyable()
                                        ->prefix('NIPPAM: '),

                                    Infolists\Components\TextEntry::make('employmentStatus.name')
                                        ->label('Status Kepegawaian')
                                        ->badge()
                                        ->color('success')
                                        ->alignCenter()
                                        ->extraAttributes(['class' => 'mt-4']),

                                    Infolists\Components\TextEntry::make('position.name')
                                        ->label('Jabatan Utama')
                                        ->icon('heroicon-o-briefcase')
                                        ->weight('medium')
                                        ->color('primary'),

                                    Infolists\Components\Section::make('Ringkasan Layanan')
                                        ->collapsible()
                                        ->collapsed()
                                        ->compact()
                                        ->schema([
                                            Infolists\Components\TextEntry::make('department.name')
                                                ->label('Unit Kerja / Bagian')
                                                ->icon('heroicon-o-building-office-2'),
                                            Infolists\Components\TextEntry::make('entry_date')
                                                ->label('Tanggal Masuk')
                                                ->date('d F Y')
                                                ->icon('heroicon-o-calendar-days'),
                                            Infolists\Components\TextEntry::make('retirement')
                                                ->label('Prediksi Pensiun')
                                                ->date('d F Y')
                                                ->icon('heroicon-o-clock')
                                                ->color('danger')
                                                ->weight('bold'),
                                            Infolists\Components\TextEntry::make('age')
                                                ->label('Usia Saat Ini')
                                                ->suffix(' Tahun')
                                                ->icon('heroicon-o-user-circle'),
                                        ]),
                                ]),
                        ])->columnSpan(1),

                        // Main Content: Tabbed Information
                        Infolists\Components\Group::make([
                            Infolists\Components\Tabs::make('Informasi Lengkap Pegawai')
                                ->tabs([
                                    // Tab 1: Data Personal
                                    Infolists\Components\Tabs\Tab::make('Data Personal')
                                        ->icon('heroicon-o-identification')
                                        ->schema([
                                            Infolists\Components\Grid::make(2)
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('id_number')->label('NIK (KTP)')->icon('heroicon-o-finger-print'),
                                                    Infolists\Components\TextEntry::make('gender')
                                                        ->label('Jenis Kelamin')
                                                        ->icon('heroicon-o-users')
                                                        ->formatStateUsing(fn(string $state): string => $state === 'male' ? 'Laki-laki' : 'Perempuan'),
                                                    Infolists\Components\TextEntry::make('birth_info')
                                                        ->label('Tempat, Tgl Lahir')
                                                        ->state(fn($record) => "{$record->place_birth}, " . $record->date_birth?->format('d F Y')),
                                                    Infolists\Components\TextEntry::make('marital_status')->label('Status Perkawinan'),
                                                    Infolists\Components\TextEntry::make('email')->label('Email Pribadi')->icon('heroicon-o-envelope'),
                                                    Infolists\Components\TextEntry::make('phone_number')->label('No. Telepon')->icon('heroicon-o-phone'),
                                                ]),
                                            Infolists\Components\TextEntry::make('address')->label('Alamat Sesuai KTP')->prose(),

                                            // History: Family
                                            Infolists\Components\Section::make('Data Anggota Keluarga')
                                                ->icon('heroicon-o-user-group')
                                                ->schema([
                                                    Infolists\Components\ViewEntry::make('family_data')
                                                        ->view('filament.components.family-data-table')
                                                        ->hiddenLabel()
                                                        ->hidden(fn(Employee $record) => $record->families->isEmpty()),
                                                ])->compact()->collapsed(),
                                        ]),

                                    // Tab 2: Kepegawaian & Karir
                                    Infolists\Components\Tabs\Tab::make('Kepegawaian')
                                        ->icon('heroicon-o-academic-cap')
                                        ->schema([
                                            Infolists\Components\Grid::make(2)
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('department.name')->label('Unit Kerja'),
                                                    Infolists\Components\TextEntry::make('subDepartment.name')->label('Sub Unit Kerja'),
                                                    Infolists\Components\TextEntry::make('education.name')->label('Pendidikan Terakhir')->badge()->color('info'),
                                                    Infolists\Components\TextEntry::make('grade.name')->label('Golongan')->badge()->color('success'),
                                                    Infolists\Components\TextEntry::make('serviceGrade.service_grade')
                                                        ->label('Masa Kerja Golongan (MKG)')
                                                        ->suffix(' Tahun'),
                                                    Infolists\Components\TextEntry::make('permanent_appointment_date')
                                                        ->label('Tanggal Pengangkatan Tetap')
                                                        ->date('d F Y'),
                                                    Infolists\Components\TextEntry::make('formatted_length_service')
                                                        ->label('Masa Kerja')
                                                        ->weight('bold')
                                                        ->color('primary'),
                                                    Infolists\Components\TextEntry::make('office_email')->label('Email Kantor')->icon('heroicon-o-envelope-open')->copyable(),
                                                ]),

                                            Infolists\Components\Section::make('Progres Rekrutmen / Orientasi')
                                                ->schema([
                                                    Infolists\Components\ViewEntry::make('recruitment_progress')
                                                        ->view('filament.components.recruitment-progress-bar')
                                                        ->hiddenLabel(),
                                                ])->compact(),

                                            Infolists\Components\Section::make('Riwayat Kontrak & Perjanjian')
                                                ->schema([
                                                    Infolists\Components\ViewEntry::make('agreement_data')
                                                        ->view('filament.components.agreement-data-table')
                                                        ->hiddenLabel()
                                                        ->hidden(fn(Employee $record) => $record->employeeAgreements->isEmpty()),
                                                ])->compact(),
                                        ]),

                                    // Tab 3: Finansial & BPJS
                                    Infolists\Components\Tabs\Tab::make('Finansial & Asuransi')
                                        ->icon('heroicon-o-banknotes')
                                        ->schema([
                                            Infolists\Components\Grid::make(2)
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('basic_salary_amount')->label('Gaji Pokok')->money('IDR')->weight('bold')->color('primary'),
                                                    Infolists\Components\TextEntry::make('bank_account_number')->label('No. Rekening Bank')->icon('heroicon-o-credit-card'),
                                                    Infolists\Components\TextEntry::make('npwp_number')->label('NPWP'),
                                                ]),

                                            Infolists\Components\Grid::make(3)
                                                ->schema([
                                                    // BPJS TK
                                                    Infolists\Components\Section::make('BPJS Ketenagakerjaan')
                                                        ->schema([
                                                            Infolists\Components\TextEntry::make('bpjs_tk_number')->label('Nomor')->copyable(),
                                                            Infolists\Components\TextEntry::make('bpjs_tk_status')->label('Status')->badge()->color(fn(?string $state): string => match (strtolower($state ?? '')) {
                                                                'aktif' => 'success',
                                                                default => 'danger',
                                                            }),
                                                        ])->columnSpan(1)->compact(),
                                                    // BPJS KES
                                                    Infolists\Components\Section::make('BPJS Kesehatan')
                                                        ->schema([
                                                            Infolists\Components\TextEntry::make('bpjs_kes_number')->label('Nomor')->copyable(),
                                                            Infolists\Components\TextEntry::make('bpjs_kes_status')->label('Status')->badge()->color(fn(?string $state): string => match (strtolower($state ?? '')) {
                                                                'aktif' => 'success',
                                                                default => 'danger',
                                                            }),
                                                            Infolists\Components\TextEntry::make('bpjs_kes_class')->label('Kelas'),
                                                        ])->columnSpan(1)->compact(),
                                                    // DAPENMA
                                                    Infolists\Components\Section::make('Dana Pensiun')
                                                        ->schema([
                                                            Infolists\Components\TextEntry::make('dapenma_number')->label('No. Dapenma'),
                                                            Infolists\Components\TextEntry::make('dapenma_phdp')->label('PHDP')->money('IDR'),
                                                            Infolists\Components\TextEntry::make('dapenma_status')->label('Status')->badge()->color(fn(?string $state): string => match (strtolower($state ?? '')) {
                                                                'aktif' => 'success',
                                                                default => 'danger',
                                                            }),
                                                        ])->columnSpan(1)->compact(),
                                                ]),
                                        ]),

                                    // Tab 4: Riwayat Karir & SK
                                    Infolists\Components\Tabs\Tab::make('Riwayat Karir')
                                        ->icon('heroicon-o-arrow-path')
                                        ->schema([
                                            Infolists\Components\Section::make('Histori Mutasi')
                                                ->icon('heroicon-o-arrow-path')
                                                ->schema([
                                                    Infolists\Components\ViewEntry::make('mutation_data')
                                                        ->view('filament.components.mutation-data-table')
                                                        ->hiddenLabel(),
                                                ])->compact()->collapsible()->collapsed(),

                                            Infolists\Components\Section::make('Histori Pergerakan Karir')
                                                ->icon('heroicon-o-briefcase')
                                                ->schema([
                                                    Infolists\Components\ViewEntry::make('career_movement_data')
                                                        ->view('filament.components.career-movement-table')
                                                        ->hiddenLabel(),
                                                ])->compact()->collapsible()->collapsed(),

                                            Infolists\Components\Section::make('Histori Kenaikan Golongan / Promosi')
                                                ->icon('heroicon-o-chevron-double-up')
                                                ->schema([
                                                    Infolists\Components\ViewEntry::make('grade_promotion_data')
                                                        ->view('filament.components.promotion-history-table')
                                                        ->hiddenLabel(),
                                                ])->compact()->collapsible()->collapsed(),

                                            Infolists\Components\Section::make('Histori Kenaikan Gaji Berkala (KGB)')
                                                ->icon('heroicon-o-banknotes')
                                                ->schema([
                                                    Infolists\Components\ViewEntry::make('periodic_salary_increase_data')
                                                        ->view('filament.components.periodic-salary-increase-table')
                                                        ->hiddenLabel(),
                                                ])->compact()->collapsible()->collapsed(),

                                            Infolists\Components\Section::make('Histori Pengangkatan')
                                                ->icon('heroicon-o-check-badge')
                                                ->schema([
                                                    Infolists\Components\ViewEntry::make('appointment_data')
                                                        ->view('filament.components.appointment-history-table')
                                                        ->hiddenLabel(),
                                                ])->compact()->collapsible()->collapsed(),
                                        ]),

                                    // Tab 5: Surat Tugas & Kedinasan
                                    Infolists\Components\Tabs\Tab::make('Surat Tugas & SPPD')
                                        ->icon('heroicon-o-document-text')
                                        ->schema([
                                            Infolists\Components\Section::make('Histori Surat Tugas (Internal)')
                                                ->schema([
                                                    Infolists\Components\ViewEntry::make('assignment_history')
                                                        ->view('filament.components.assignment-history-table')
                                                        ->hiddenLabel(),
                                                ])->compact(),

                                            Infolists\Components\Section::make('Histori SPPD (Perjalanan Dinas)')
                                                ->schema([
                                                    Infolists\Components\ViewEntry::make('business_travel_history')
                                                        ->view('filament.components.business-travel-history-table')
                                                        ->hiddenLabel(),
                                                ])->compact(),
                                        ]),

                                    Infolists\Components\Tabs\Tab::make('Riwayat Presensi')
                                        ->icon('heroicon-o-clock')
                                        ->schema([
                                            Infolists\Components\Section::make('Log Mesin Presensi')
                                                ->description('15 Rekaman scan terakhir dari mesin absensi')
                                                ->schema([
                                                    Infolists\Components\ViewEntry::make('attendance_data')
                                                        ->view('filament.components.attendance-history-table')
                                                        ->hiddenLabel(),
                                                ])->compact(),
                                        ]),
                                ])
                                ->persistTabInQueryString(),
                        ])->columnSpan(2),
                    ]),
            ]);
    }

    private static function getEmployeeImportHeaders(): array
    {
        return [
            'id',
            'nippam',
            'pin',
            'name',
            'gender',
            'place_birth',
            'date_birth',
            'religion',
            'marital_status',
            'blood_type',
            'email',
            'office_email',
            'phone_number',
            'id_number',
            'familycard_number',
            'npwp_number',
            'bank_account_number',
            'bpjs_tk_number',
            'bpjs_tk_status',
            'bpjs_kes_number',
            'bpjs_kes_status',
            'bpjs_kes_class',
            'rek_dplk_pribadi',
            'rek_dplk_bersama',
            'dapenma_number',
            'dapenma_phdp',
            'dapenma_status',
            'address',
            'work_unit_type',
            'work_unit_name',
            'sub_department_name',
            'position_name',
            'employment_status_name',
            'education_name',
            'grade_name',
            'mkg_years',
            'agreement_type_name',
            'entry_date',
            'probation_appointment_date',
            'leave_balance',
            'agreement_date_start',
            'agreement_date_end',
            'grade_date_start',
            'grade_date_end',
            'periodic_salary_date_start',
            'periodic_salary_date_end',
        ];
    }

    public static function getEmployeeImportColumnLabels(): array
    {
        return [
            'id' => 'ID Pegawai',
            'nippam' => 'NIPPAM',
            'pin' => 'PIN Absensi',
            'name' => 'Nama Pegawai',
            'gender' => 'Jenis Kelamin',
            'place_birth' => 'Tempat Lahir',
            'date_birth' => 'Tanggal Lahir',
            'religion' => 'Agama',
            'marital_status' => 'Status Perkawinan',
            'blood_type' => 'Golongan Darah',
            'email' => 'Email Pribadi',
            'office_email' => 'Email Kantor',
            'phone_number' => 'No. HP',
            'id_number' => 'NIK (KTP)',
            'familycard_number' => 'No. Kartu Keluarga',
            'npwp_number' => 'NPWP',
            'bank_account_number' => 'No. Rekening',
            'bpjs_tk_number' => 'No. BPJS TK',
            'bpjs_tk_status' => 'Status BPJS TK',
            'bpjs_kes_number' => 'No. BPJS Kesehatan',
            'bpjs_kes_status' => 'Status BPJS Kesehatan',
            'bpjs_kes_class' => 'Kelas BPJS Kesehatan',
            'rek_dplk_pribadi' => 'Rek DPLK Pribadi',
            'rek_dplk_bersama' => 'Rek DPLK Bersama',
            'dapenma_number' => 'No. Dapenma',
            'dapenma_phdp' => 'PHDP Dapenma',
            'dapenma_status' => 'Status Dapenma',
            'address' => 'Alamat',
            'work_unit_type' => 'Tipe Unit Kerja',
            'work_unit_name' => 'Nama Unit Kerja',
            'sub_department_name' => 'Sub Departemen',
            'position_name' => 'Jabatan',
            'employment_status_name' => 'Status Kepegawaian',
            'education_name' => 'Pendidikan',
            'grade_name' => 'Golongan',
            'mkg_years' => 'MKG (Tahun)',
            'agreement_type_name' => 'Jenis Perjanjian',
            'entry_date' => 'Tanggal Masuk',
            'probation_appointment_date' => 'TMT Calon Pegawai',
            'leave_balance' => 'Saldo Cuti',
            'agreement_date_start' => 'Mulai Perjanjian',
            'agreement_date_end' => 'Akhir Perjanjian',
            'grade_date_start' => 'Mulai Golongan',
            'grade_date_end' => 'Akhir Golongan',
            'periodic_salary_date_start' => 'Mulai Gaji Berkala',
            'periodic_salary_date_end' => 'Akhir Gaji Berkala',
        ];
    }

    public static function getEmployeeImportDateColumns(): array
    {
        return [
            'date_birth',
            'entry_date',
            'probation_appointment_date',
            'agreement_date_start',
            'agreement_date_end',
            'grade_date_start',
            'grade_date_end',
            'periodic_salary_date_start',
            'periodic_salary_date_end',
        ];
    }

    public static function formatEmployeeImportPreviewValue(string $column, mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $valueString = trim((string) $value);
        if ($valueString === '') {
            return '';
        }

        if (in_array($column, static::getEmployeeImportDateColumns(), true)) {
            $normalizedDate = static::normalizeEmployeeImportDate($value);
            if ($normalizedDate !== null) {
                return \Carbon\Carbon::parse($normalizedDate)->format('d-m-Y');
            }
        }

        return $valueString;
    }

    private static function getEmployeeImportInstructionRow(): array
    {
        return [
            'KOSONGKAN untuk pegawai baru / ISI ID untuk update',
            'Opsional (otomatis jika kosong)',
            'Opsional, angka 1-999 (auto-generate jika kosong)',
            'Wajib',
            'male / female',
            'Disarankan diisi',
            'dd-mm-yyyy',
            'Opsional',
            'single / married / divorced / widowed',
            'Opsional (A/B/O/AB)',
            'Email valid',
            'Email kantor valid',
            'No HP / Telp',
            '16 digit',
            '16 digit',
            'Format: 12.345.678.9-012.345',
            'Angka / teks rekening',
            'Angka',
            'Aktif / Non-Aktif',
            'Angka',
            'Aktif / Non-Aktif',
            'Kelas 1 / Kelas 2 / Kelas 3',
            'Angka',
            'Angka',
            'Opsional',
            'Angka',
            'Aktif / Non-Aktif',
            'Alamat lengkap',
            'bagian / cabang / unit',
            'Nama unit atau ID master unit',
            'Nama atau ID',
            'Nama atau ID',
            'Nama atau ID',
            'Nama atau ID',
            'Nama atau ID',
            'MKG (tahun) atau ID',
            'Nama atau ID',
            'dd-mm-yyyy',
            'dd-mm-yyyy',
            'Angka, default 12',
            'dd-mm-yyyy',
            'dd-mm-yyyy',
            'dd-mm-yyyy',
            'dd-mm-yyyy',
            'dd-mm-yyyy',
            'dd-mm-yyyy',
        ];
    }

    private static function buildEmployeeImportInstructionMap(array $headers): array
    {
        $instructionRow = static::getEmployeeImportInstructionRow();
        $instructionByHeader = [];

        foreach ($headers as $index => $header) {
            $headerKey = strtolower(trim((string) $header));
            if ($headerKey === '') {
                continue;
            }

            $instructionByHeader[$headerKey] = static::normalizeEmployeeImportCellValue($instructionRow[$index] ?? '');
        }

        return $instructionByHeader;
    }

    private static function isEmployeeImportInstructionRow(array $rowData, array $instructionByHeader): bool
    {
        if (empty($instructionByHeader)) {
            return false;
        }

        $idCell = static::normalizeEmployeeImportCellValue($rowData['id'] ?? '');
        $nameCell = static::normalizeEmployeeImportCellValue($rowData['name'] ?? '');

        if (
            $idCell !== '' &&
            str_contains($idCell, 'kosongkan') &&
            (str_contains($idCell, 'pegawai baru') || str_contains($idCell, 'untuk update')) &&
            $nameCell === 'wajib'
        ) {
            return true;
        }

        $comparableFieldCount = 0;
        $matchedFieldCount = 0;

        foreach ($instructionByHeader as $header => $instructionValue) {
            if ($instructionValue === '') {
                continue;
            }

            $comparableFieldCount++;
            $rowValue = static::normalizeEmployeeImportCellValue($rowData[$header] ?? '');

            if ($rowValue === $instructionValue) {
                $matchedFieldCount++;
            }
        }

        if ($comparableFieldCount === 0) {
            return false;
        }

        $matchRatio = $matchedFieldCount / $comparableFieldCount;

        return $matchRatio >= 0.8;
    }

    private static function normalizeEmployeeImportCellValue($value): string
    {
        $normalized = (string) $value;
        $normalized = str_replace("\xEF\xBB\xBF", '', $normalized);
        $normalized = str_replace("\xC2\xA0", ' ', $normalized);
        $normalized = trim($normalized);
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        return strtolower($normalized);
    }

    private static function buildEmployeeImportDuplicateMessage(string $errorMessage): string
    {
        if (!preg_match("/Duplicate entry '(.+?)' for key '([^']+)'/i", $errorMessage, $matches)) {
            return "Data ini sudah terdaftar (duplikasi nilai unik).";
        }

        $duplicateValue = $matches[1];
        $constraintKey = $matches[2];
        $lowerConstraintKey = strtolower($constraintKey);

        $columnLabels = [
            'pin' => 'PIN Absensi',
            'nippam' => 'NIPPAM',
            'office_email' => 'Email Kantor',
            'email' => 'Email',
            'username' => 'Username',
            'id_number' => 'NIK (KTP)',
        ];

        foreach ($columnLabels as $columnKey => $columnLabel) {
            if (str_contains($lowerConstraintKey, $columnKey)) {
                return "Duplikasi {$columnLabel}: '{$duplicateValue}' sudah digunakan pegawai lain.";
            }
        }

        return "Data duplikat terdeteksi pada constraint '{$constraintKey}' dengan nilai '{$duplicateValue}'.";
    }

    public static function getEmployeeImportPreviewCacheKey(?int $userId = null): string
    {
        $resolvedUserId = $userId ?? (auth()->id() ?? 0);
        return "employee_import_preview_payload_user_{$resolvedUserId}";
    }

    public static function getEmployeeImportPreviewPayload(?int $userId = null): array
    {
        return Cache::get(static::getEmployeeImportPreviewCacheKey($userId), []);
    }

    public static function getEmployeeImportPreviewViewData(?int $userId = null): array
    {
        $previewPayload = static::getEmployeeImportPreviewPayload($userId);
        $processedData = $previewPayload['processed_data'] ?? [];
        $warnings = $previewPayload['warnings'] ?? [];

        $headers = [];
        if (!empty($processedData)) {
            $headers = array_keys($processedData[0]['row'] ?? []);
        }

        $generatedAt = $previewPayload['generated_at'] ?? null;
        $generatedAtLabel = null;
        if (is_string($generatedAt) && trim($generatedAt) !== '') {
            try {
                $generatedAtLabel = \Carbon\Carbon::parse($generatedAt)
                    ->setTimezone('Asia/Jakarta')
                    ->format('d-m-Y H:i:s');
            } catch (\Throwable $e) {
                $generatedAtLabel = $generatedAt;
            }
        }

        return [
            'processedData' => $processedData,
            'warnings' => $warnings,
            'headers' => $headers,
            'columnLabels' => static::getEmployeeImportColumnLabels(),
            'summary' => [
                'total_rows' => count($processedData),
                'created' => count(array_filter($processedData, fn($item) => (bool) ($item['is_new'] ?? false))),
                'updated' => count(array_filter($processedData, fn($item) => !(bool) ($item['is_new'] ?? false))),
                'auto_pin' => count(array_filter($processedData, fn($item) => ($item['pin_source'] ?? 'input') === 'generated')),
            ],
            'generatedAt' => $generatedAt,
            'generatedAtLabel' => $generatedAtLabel,
        ];
    }

    private static function normalizeEmployeeImportDate(mixed $dateValue): ?string
    {
        if ($dateValue === null || $dateValue === '') {
            return null;
        }

        if (is_numeric($dateValue)) {
            try {
                return SpreadsheetDate::excelToDateTimeObject((float) $dateValue)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $dateString = trim((string) $dateValue);
        if ($dateString === '') {
            return null;
        }

        $formats = ['d-m-Y', 'd/m/Y', 'd.m.Y', 'Y-m-d'];
        foreach ($formats as $format) {
            try {
                $parsedDate = \Carbon\Carbon::createFromFormat($format, $dateString);
                if ($parsedDate !== false) {
                    return $parsedDate->format('Y-m-d');
                }
            } catch (\Exception $e) {
            }
        }

        return null;
    }

    public static function runEmployeeImportCommit(array $processedData, array $warnings): bool
    {
        if (empty($processedData)) {
            Notification::make()
                ->title('Preview Kosong')
                ->body('Tidak ada data preview yang bisa disimpan.')
                ->warning()
                ->persistent()
                ->send();
            return false;
        }

        $stats = ['created' => 0, 'updated' => 0];
        $currentRowName = 'N/A';
        $currentRowIndex = 0;
        $authId = auth()->id() ?? 1;
        $now = now();

        $targetUpdateIds = [];
        foreach ($processedData as $data) {
            $isNewRow = (bool) ($data['is_new'] ?? empty(($data['row']['id'] ?? null)));
            if ($isNewRow) {
                continue;
            }

            $targetEmployeeId = trim((string) ($data['row']['id'] ?? ''));
            if ($targetEmployeeId === '' || !ctype_digit($targetEmployeeId)) {
                continue;
            }

            $targetUpdateIds[] = (int) $targetEmployeeId;
        }

        $targetUpdateIds = array_values(array_unique($targetUpdateIds));
        $existingEmployeeById = [];
        if (!empty($targetUpdateIds)) {
            $existingEmployeeById = \App\Models\Employee::query()
                ->whereIn('id', $targetUpdateIds)
                ->get()
                ->keyBy('id')
                ->all();
        }

        try {
            \Spatie\Activitylog\Facades\Activity::withoutLogs(function () use (
                $processedData,
                &$stats,
                &$currentRowName,
                &$currentRowIndex,
                $authId,
                $now,
                $existingEmployeeById
            ) {
                \Illuminate\Support\Facades\DB::transaction(function () use (
                    $processedData,
                    &$stats,
                    &$currentRowName,
                    &$currentRowIndex,
                    $authId,
                    $now,
                    $existingEmployeeById
                ) {
                    foreach ($processedData as $data) {
                        $rowData = $data['row'];
                        $ids = $data['ids'];
                        $actualRowInFile = $data['line_number'];
                        $effectivePin = $data['effective_pin'] ?? null;

                        $currentRowName = $rowData['name'] ?? ($rowData['nippam'] ?? 'N/A');
                        $currentRowIndex = $actualRowInFile;

                        $isNew = (bool) ($data['is_new'] ?? empty($rowData['id']));
                        if ($isNew) {
                            $stats['created']++;
                        } else {
                            $stats['updated']++;
                        }

                        $clean = fn($val) => ($val === '' || $val === null) ? null : trim((string) $val);

                        $gender = strtolower(trim($rowData['gender'] ?? ''));
                        $gender = in_array($gender, ['male', 'female']) ? $gender : 'male';

                        $marital = strtolower(trim($rowData['marital_status'] ?? ''));
                        $marital = in_array($marital, ['single', 'married', 'divorced', 'widowed']) ? $marital : 'single';

                        $employeeData = [
                            'name' => $rowData['name'],
                            'pin' => $clean($effectivePin),
                            'gender' => $gender,
                            'religion' => $clean($rowData['religion'] ?? null),
                            'place_birth' => $clean($rowData['place_birth'] ?? null),
                            'date_birth' => static::normalizeEmployeeImportDate($rowData['date_birth'] ?? ''),
                            'marital_status' => $marital,
                            'blood_type' => $clean($rowData['blood_type'] ?? null),
                            'email' => $clean($rowData['email'] ?? null),
                            'office_email' => $clean($rowData['office_email'] ?? null),
                            'phone_number' => $clean($rowData['phone_number'] ?? null),
                            'id_number' => $clean($rowData['id_number'] ?? null),
                            'familycard_number' => $clean($rowData['familycard_number'] ?? null),
                            'npwp_number' => $clean($rowData['npwp_number'] ?? null),
                            'bank_account_number' => $clean($rowData['bank_account_number'] ?? null),
                            'bpjs_tk_number' => $clean($rowData['bpjs_tk_number'] ?? null),
                            'bpjs_tk_status' => $clean($rowData['bpjs_tk_status'] ?? 'Aktif'),
                            'bpjs_kes_number' => $clean($rowData['bpjs_kes_number'] ?? null),
                            'bpjs_kes_status' => $clean($rowData['bpjs_kes_status'] ?? 'Aktif'),
                            'bpjs_kes_class' => $clean($rowData['bpjs_kes_class'] ?? null),
                            'rek_dplk_pribadi' => $clean($rowData['rek_dplk_pribadi'] ?? null),
                            'rek_dplk_bersama' => $clean($rowData['rek_dplk_bersama'] ?? null),
                            'dapenma_number' => $clean($rowData['dapenma_number'] ?? null),
                            'dapenma_phdp' => (isset($rowData['dapenma_phdp']) && $rowData['dapenma_phdp'] !== '') ? $rowData['dapenma_phdp'] : null,
                            'dapenma_status' => $clean($rowData['dapenma_status'] ?? 'Aktif'),
                            'address' => $clean($rowData['address'] ?? null),
                            'departments_id' => $ids['dept_id'],
                            'bagian_id' => $ids['bagian_id'],
                            'cabang_id' => $ids['cabang_id'],
                            'unit_id' => $ids['unit_id'],
                            'sub_department_id' => $ids['sub_dept_id'],
                            'employee_position_id' => $ids['pos_id'],
                            'employment_status_id' => $ids['status_id'],
                            'employee_education_id' => $ids['edu_id'],
                            'basic_salary_id' => $ids['grade_id'],
                            'employee_service_grade_id' => $ids['mkg_id'],
                            'master_employee_agreement_id' => $ids['agreement_id'],
                            'entry_date' => static::normalizeEmployeeImportDate($rowData['entry_date'] ?? ''),
                            'probation_appointment_date' => static::normalizeEmployeeImportDate($rowData['probation_appointment_date'] ?? ''),
                            'leave_balance' => $rowData['leave_balance'] ?? 12,
                            'agreement_date_start' => static::normalizeEmployeeImportDate($rowData['agreement_date_start'] ?? ''),
                            'agreement_date_end' => static::normalizeEmployeeImportDate($rowData['agreement_date_end'] ?? ''),
                            'grade_date_start' => static::normalizeEmployeeImportDate($rowData['grade_date_start'] ?? ''),
                            'grade_date_end' => static::normalizeEmployeeImportDate($rowData['grade_date_end'] ?? ''),
                            'periodic_salary_date_start' => static::normalizeEmployeeImportDate($rowData['periodic_salary_date_start'] ?? ''),
                            'periodic_salary_date_end' => static::normalizeEmployeeImportDate($rowData['periodic_salary_date_end'] ?? ''),
                            'users_id' => $authId,
                        ];

                        if (!empty($rowData['nippam'])) {
                            $employeeData['nippam'] = $rowData['nippam'];
                        }

                        $employee = null;
                        $targetEmployeeId = trim((string) ($rowData['id'] ?? ''));
                        if (
                            !$isNew &&
                            $targetEmployeeId !== '' &&
                            ctype_digit($targetEmployeeId) &&
                            isset($existingEmployeeById[(int) $targetEmployeeId])
                        ) {
                            $employee = $existingEmployeeById[(int) $targetEmployeeId];
                            $employee->fill($employeeData);
                            $employee->save();
                        } else {
                            $employee = \App\Models\Employee::create($employeeData);
                        }

                        if ($isNew) {
                            $user = \App\Models\User::create([
                                'name' => $employee->name,
                                'email' => $employee->email ?: "pegawai_{$employee->id}@pdam.com",
                                'password' => \Illuminate\Support\Facades\Hash::make('pdam891706'),
                                'is_verified' => true,
                            ]);

                            \App\Models\Employee::query()
                                ->whereKey($employee->id)
                                ->update(['users_id' => $user->id]);

                            \App\Models\EmployeeAppointment::create([
                                'employee_id' => $employee->id,
                                'decision_letter_number' => 'SK-IMPORT-' . now()->format('YmdHis'),
                                'appointment_date' => $employee->entry_date ?: $now,
                                'new_employment_status_id' => $employee->employment_status_id ?: 1,
                                'employee_grade_id' => $employee->basic_salary_id,
                                'is_applied' => true,
                                'applied_at' => $now,
                                'users_id' => $authId,
                            ]);
                        }
                    }
                });
            });

            Notification::make()
                ->title('Proses Berhasil')
                ->body("Berhasil: {$stats['created']} pegawai baru, {$stats['updated']} pegawai diperbarui.")
                ->success()->send();

            if (!empty($warnings)) {
                $warningPreview = implode('<br>', array_slice($warnings, 0, 10));
                $warningFooter = count($warnings) > 10
                    ? '<br><br><strong>...dan ' . (count($warnings) - 10) . ' baris lainnya.</strong>'
                    : '';

                Notification::make()
                    ->title('Import selesai dengan catatan MKG')
                    ->body(new \Illuminate\Support\HtmlString($warningPreview . $warningFooter))
                    ->warning()
                    ->persistent()
                    ->send();
            }

            return true;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $friendlyMessage = "Terjadi kesalahan sistem saat menyimpan data.";

            if (str_contains($errorMessage, 'Duplicate entry')) {
                $friendlyMessage = static::buildEmployeeImportDuplicateMessage($errorMessage);
            } elseif (str_contains($errorMessage, 'Data truncated')) {
                $friendlyMessage = "Format data tidak sesuai dengan batasan sistem (Data terlalu panjang atau pilihan tidak valid).";
            }

            \Illuminate\Support\Facades\Log::error("Import Error: {$errorMessage} | Row: {$currentRowIndex} | Name: {$currentRowName}");

            Notification::make()
                ->title('Proses Gagal')
                ->body(new \Illuminate\Support\HtmlString("
                    Terjadi kesalahan pada data <strong>{$currentRowName}</strong> (Baris {$currentRowIndex}).<br><br>
                    <strong>Pesan:</strong> {$friendlyMessage}
                "))
                ->danger()
                ->persistent()
                ->send();

            return false;
        }
    }

    private static function getEmployeeImportSampleRow(): array
    {
        $edu = \App\Models\MasterEmployeeEducation::query()->orderBy('name')->first()?->name ?? 'S1';
        $pos = \App\Models\MasterEmployeePosition::query()->orderBy('name')->first()?->name ?? 'Staff';
        $grade = \App\Models\MasterEmployeeGrade::query()->orderBy('name')->first()?->name ?? 'A1';
        $status = \App\Models\MasterEmployeeStatusEmployment::query()->orderBy('name')->first()?->name ?? 'Pegawai Tetap';
        $dept = \App\Models\MasterDepartment::query()->orderBy('name')->first();
        $agreement = \App\Models\MasterEmployeeAgreement::query()->orderBy('name')->first()?->name ?? 'PKWTT';
        $mkg = (string) (\App\Models\MasterEmployeeServiceGrade::query()->orderBy('service_grade')->value('service_grade') ?? '0');

        return [
            '',
            '123456789',
            '101',
            'Budi Santoso',
            'male',
            'Jakarta',
            '30-01-1990',
            'Islam',
            'single',
            'O',
            'budi@example.com',
            'budi.pdam@example.com',
            '08123456789',
            '1234567890123456',
            '1234567890123456',
            '12.345.678.9-012.000',
            '1234567890',
            '00012345678',
            'Aktif',
            '0001234567890',
            'Aktif',
            'Kelas 1',
            '1234567890',
            '1234567890',
            '12345',
            '5000000',
            'Aktif',
            'Jl. Contoh No. 123',
            $dept?->type ?? 'bagian',
            $dept?->name ?? 'Bagian Umum',
            'Sub Bagian Umum',
            $pos,
            $status,
            $edu,
            $grade,
            $mkg,
            $agreement,
            '01-01-2024',
            '01-07-2024',
            '12',
            '01-01-2024',
            '31-12-2024',
            '01-01-2024',
            '01-01-2026',
            '01-01-2024',
            '01-01-2026',
        ];
    }

    private static function getEmployeeImportReferenceRows(): array
    {
        return [
            ['id', 'Opsional', 'Isi jika update pegawai lama, kosongkan untuk pegawai baru', 'Jika ID kosong tetapi NIPPAM cocok, data akan di-update otomatis berdasarkan NIPPAM'],
            ['name', 'Wajib', 'Nama lengkap pegawai', 'Jika kosong import ditolak'],
            ['pin', 'Opsional', 'Harus unik, angka 1-999', 'Jika kosong akan auto-generate dalam rentang 1-999'],
            ['nippam', 'Opsional', 'Harus unik jika diisi', 'Boleh kosong untuk auto-generate sistem; dipakai sebagai fallback update saat ID kosong'],
            ['gender', 'Disarankan', 'male / female', 'Jika tidak valid, default: male'],
            ['marital_status', 'Disarankan', 'single / married / divorced / widowed', 'Jika tidak valid, default: single'],
            ['date_birth dan kolom tanggal lain', 'Opsional', 'Format tanggal: dd-mm-yyyy', 'Juga menerima d/m/Y, d.m.Y, Y-m-d'],
            ['id_number', 'Opsional', '16 digit', 'Nomor KTP'],
            ['familycard_number', 'Opsional', '16 digit', 'Nomor Kartu Keluarga'],
            ['npwp_number', 'Opsional', 'Format: XX.XXX.XXX.X-XXX.XXX', 'Contoh: 12.345.678.9-012.345'],
            ['bpjs_tk_status / bpjs_kes_status / dapenma_status', 'Opsional', 'Aktif / Non-Aktif', 'Perhatikan ejaan'],
            ['bpjs_kes_class', 'Opsional', 'Kelas 1 / Kelas 2 / Kelas 3', 'Perhatikan spasi'],
            ['work_unit_type', 'Opsional', 'bagian / cabang / unit', 'Wajib konsisten jika work_unit_name diisi'],
            ['work_unit_name', 'Opsional', 'Nama unit kerja atau ID MasterDepartment', 'Jika tidak ditemukan import ditolak'],
            ['sub_department_name', 'Opsional', 'Nama sub-departemen atau ID', 'Mengacu ke master sub-departemen'],
            ['position_name', 'Opsional', 'Nama jabatan atau ID', 'Mengacu ke master jabatan'],
            ['employment_status_name', 'Opsional', 'Nama status pegawai atau ID', 'Mengacu ke master status'],
            ['education_name', 'Opsional', 'Nama pendidikan atau ID', 'Mengacu ke master pendidikan'],
            ['grade_name', 'Opsional', 'Nama golongan atau ID', 'Mengacu ke master golongan'],
            ['mkg_years', 'Opsional', 'MKG (service_grade) atau ID', 'Jika tidak ditemukan, diabaikan + warning'],
            ['agreement_type_name', 'Opsional', 'Nama jenis SK/perjanjian atau ID', 'Mengacu ke master agreement'],
            ['leave_balance', 'Opsional', 'Angka', 'Default: 12 jika kosong'],
        ];
    }

    private static function readEmployeeImportRows(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            $fileHandle = fopen($path, 'r');
            if ($fileHandle === false) {
                throw new \RuntimeException('Gagal membaca file CSV yang diunggah.');
            }

            $firstLine = fgets($fileHandle);
            rewind($fileHandle);
            $delimiter = (strpos((string) $firstLine, ';') !== false && strpos((string) $firstLine, ',') === false) ? ';' : ',';

            $rows = [];
            while (($row = fgetcsv($fileHandle, 0, $delimiter)) !== false) {
                $rows[] = $row;
            }
            fclose($fileHandle);

            return $rows;
        }

        if (!in_array($extension, ['xls', 'xlsx'], true)) {
            throw new \RuntimeException('Format file tidak didukung. Gunakan CSV, XLS, atau XLSX.');
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName('Data Import') ?? $spreadsheet->getSheet(0);
        $rows = $sheet->toArray('', true, false, false);
        $spreadsheet->disconnectWorksheets();

        return $rows;
    }

    private static function downloadEmployeeImportTemplateCsv()
    {
        $headers = static::getEmployeeImportHeaders();
        $instructions = static::getEmployeeImportInstructionRow();
        $sample = static::getEmployeeImportSampleRow();

        $callback = function () use ($headers, $instructions, $sample) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, $instructions);
            fputcsv($file, $sample);
            fclose($file);
        };

        return response()->streamDownload($callback, 'template_manajemen_pegawai.csv');
    }

    private static function downloadEmployeeImportTemplateExcel()
    {
        $headers = static::getEmployeeImportHeaders();
        $instructions = static::getEmployeeImportInstructionRow();
        $sample = static::getEmployeeImportSampleRow();
        $referenceRows = static::getEmployeeImportReferenceRows();

        $spreadsheet = new Spreadsheet();
        $dataSheet = $spreadsheet->getActiveSheet();
        $dataSheet->setTitle('Data Import');
        $dataSheet->fromArray($headers, null, 'A1');
        $dataSheet->fromArray($instructions, null, 'A2');
        $dataSheet->fromArray($sample, null, 'A3');

        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $dataSheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $dataSheet->getStyle("A1:{$lastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2563EB');
        $dataSheet->getStyle("A2:{$lastColumn}2")->getFont()->getColor()->setARGB('FF92400E');
        $dataSheet->getStyle("A2:{$lastColumn}2")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFEF3C7');
        $dataSheet->getStyle("A2:{$lastColumn}2")->getAlignment()->setWrapText(true);
        $dataSheet->freezePane('A2');
        $dataSheet->setAutoFilter("A1:{$lastColumn}1");

        for ($i = 1; $i <= count($headers); $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $dataSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $referenceSheet = $spreadsheet->createSheet();
        $referenceSheet->setTitle('Referensi');
        $referenceSheet->fromArray(['Kolom', 'Status', 'Aturan', 'Catatan'], null, 'A1');
        $referenceSheet->fromArray($referenceRows, null, 'A2');
        $referenceSheet->getStyle('A1:D1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $referenceSheet->getStyle('A1:D1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F766E');
        $referenceSheet->getStyle('A1:D200')->getAlignment()->setWrapText(true);

        $rowPointer = count($referenceRows) + 4;
        $referenceSheet->setCellValue("A{$rowPointer}", 'Referensi Data Master (bisa isi Nama atau ID)');
        $referenceSheet->getStyle("A{$rowPointer}")->getFont()->setBold(true);
        $rowPointer += 1;

        $appendMasterTable = function (string $title, array $headerRow, array $rows) use ($referenceSheet, &$rowPointer) {
            $referenceSheet->setCellValue("A{$rowPointer}", $title);
            $referenceSheet->getStyle("A{$rowPointer}")->getFont()->setBold(true);
            $rowPointer++;

            $referenceSheet->fromArray($headerRow, null, "A{$rowPointer}");
            $referenceSheet->getStyle("A{$rowPointer}:C{$rowPointer}")->getFont()->setBold(true);
            $rowPointer++;

            if (empty($rows)) {
                $referenceSheet->setCellValue("A{$rowPointer}", '-');
                $rowPointer += 2;
                return;
            }

            foreach ($rows as $row) {
                $referenceSheet->fromArray($row, null, "A{$rowPointer}");
                $rowPointer++;
            }

            $rowPointer++;
        };

        $appendMasterTable(
            'Master Unit Kerja',
            ['ID', 'Nama Unit', 'Tipe'],
            \App\Models\MasterDepartment::query()->orderBy('name')->get()->map(fn($m) => [$m->id, $m->name, $m->type])->toArray()
        );
        $appendMasterTable(
            'Master Sub Departemen',
            ['ID', 'Nama Sub Departemen', ''],
            \App\Models\MasterSubDepartment::query()->orderBy('name')->get()->map(fn($m) => [$m->id, $m->name, ''])->toArray()
        );
        $appendMasterTable(
            'Master Jabatan',
            ['ID', 'Nama Jabatan', ''],
            \App\Models\MasterEmployeePosition::query()->orderBy('name')->get()->map(fn($m) => [$m->id, $m->name, ''])->toArray()
        );
        $appendMasterTable(
            'Master Status Pegawai',
            ['ID', 'Status', ''],
            \App\Models\MasterEmployeeStatusEmployment::query()->orderBy('name')->get()->map(fn($m) => [$m->id, $m->name, ''])->toArray()
        );
        $appendMasterTable(
            'Master Pendidikan',
            ['ID', 'Pendidikan', ''],
            \App\Models\MasterEmployeeEducation::query()->orderBy('name')->get()->map(fn($m) => [$m->id, $m->name, ''])->toArray()
        );
        $appendMasterTable(
            'Master Golongan',
            ['ID', 'Golongan', ''],
            \App\Models\MasterEmployeeGrade::query()->orderBy('name')->get()->map(fn($m) => [$m->id, $m->name, ''])->toArray()
        );
        $appendMasterTable(
            'Master MKG',
            ['ID', 'Service Grade', 'Deskripsi'],
            \App\Models\MasterEmployeeServiceGrade::query()->orderBy('service_grade')->get()->map(fn($m) => [$m->id, $m->service_grade, $m->desc])->toArray()
        );
        $appendMasterTable(
            'Master Jenis SK/Perjanjian',
            ['ID', 'Jenis SK', ''],
            \App\Models\MasterEmployeeAgreement::query()->orderBy('name')->get()->map(fn($m) => [$m->id, $m->name, ''])->toArray()
        );

        foreach (range('A', 'D') as $column) {
            $referenceSheet->getColumnDimension($column)->setAutoSize(true);
        }

        $exampleSheet = $spreadsheet->createSheet();
        $exampleSheet->setTitle('Contoh Pengisian');
        $exampleSheet->fromArray($headers, null, 'A1');
        $exampleSheet->fromArray($sample, null, 'A2');

        $updateSample = $sample;
        $updateSample[0] = '1';
        $updateSample[3] = 'Budi Santoso Update';
        $updateSample[10] = 'budi.update@example.com';
        $exampleSheet->fromArray($updateSample, null, 'A3');
        $exampleSheet->setCellValue('A5', 'Catatan: Baris 2 contoh data pegawai BARU (id kosong). Baris 3 contoh UPDATE (id diisi).');

        $exampleSheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $exampleSheet->getStyle("A1:{$lastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF7C3AED');
        $exampleSheet->freezePane('A2');
        for ($i = 1; $i <= count($headers); $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $exampleSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'template_manajemen_pegawai.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BenefitsRelationManager::class,
            RelationManagers\SalaryCutsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            EmployeeResource\Widgets\EmployeeStatsOverview::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
        // No eager loading — reference data served from getLookupData() cache.
        // This reduces DB round-trips from 9+ to 1 (cache table read) per request.
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
