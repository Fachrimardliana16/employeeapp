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

                                                Forms\Components\Select::make('sub_department_id')
                                                    ->label('Sub Bagian')
                                                    ->options(function (Forms\Get $get) {
                                                        $deptId = $get('departments_id');
                                                        if (!$deptId) return [];
                                                        return \App\Models\MasterSubDepartment::where('departments_id', $deptId)->pluck('name', 'id');
                                                    })
                                                    ->searchable()
                                                    ->preload()
                                                    ->visible(fn (Forms\Get $get) => $get('departments_id') !== null),

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
                                                    ->visible(fn (Forms\Get $get) => $get('employee_position_id') || $get('basic_salary_id'))
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
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto Pas')
                    ->circular()
                    ->disk('public'),
                Tables\Columns\TextColumn::make('nippam')
                    ->label('NIPPAM / PIN')
                    ->description(fn (Employee $record): string => $record->pin ? "PIN: " . str_repeat('*', max(0, strlen($record->pin) - 2)) . substr($record->pin, -2) : "PIN: -")
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
                    ->toggleable(),
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('unit_kerja')
                    ->label('Unit Kerja / Bagian')
                    ->getStateUsing(function (Employee $record) {
                        $unit = $record->active_organizational_unit->name ?? '-';
                        $subUnit = $record->subDepartment->name ?? '';
                        return $unit . ($subUnit ? " / " . $subUnit : "");
                    })
                    ->description(function (Employee $record) {
                        return $record->active_organizational_unit->type ?? '';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->orWhereHas('department', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('bagian', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('cabang', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('unit', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Golongan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('serviceGrade.service_grade')
                    ->label('MKG (Thn)')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('next_kgb_date')
                    ->label('KGB Berikutnya')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('next_promotion_date')
                    ->label('Golongan Berikutnya')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('basic_salary_amount')
                    ->label('Gaji Pokok')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('age')
                    ->label('Umur')
                    ->suffix(' Thn')
                    ->sortable(),
                Tables\Columns\TextColumn::make('retirement')
                    ->label('Pensiun')
                    ->date('d-m-Y')
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
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->orWhereHas('position', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('employmentStatus', fn ($q) => $q->where('name', 'like', "%{$search}%"));
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
                        return $query->orderBy('permanent_appointment_date', $direction === 'asc' ? 'desc' : 'asc');
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
                                            ->label('Pilih Bagian')
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

                Tables\Actions\Action::make('download_template')
                    ->label('Download Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function () {
                        $headers = [
                            'id', 'nippam', 'name', 'gender', 'place_birth', 'date_birth', 'religion', 'marital_status', 'blood_type',
                            'email', 'office_email', 'phone_number', 'id_number', 'familycard_number', 'npwp_number',
                            'bank_account_number', 'bpjs_tk_number', 'bpjs_tk_status', 'bpjs_kes_number', 'bpjs_kes_status',
                            'bpjs_kes_class', 'rek_dplk_pribadi', 'rek_dplk_bersama', 'dapenma_number', 'dapenma_phdp',
                            'dapenma_status', 'address', 'work_unit_type', 'work_unit_name', 'sub_department_name',
                            'position_name', 'employment_status_name', 'education_name', 'grade_name', 'mkg_years',
                            'agreement_type_name', 'entry_date', 'probation_appointment_date', 'leave_balance',
                            'agreement_date_start', 'agreement_date_end', 'grade_date_start', 'grade_date_end',
                            'periodic_salary_date_start', 'periodic_salary_date_end'
                        ];

                        $callback = function () use ($headers) {
                            $file = fopen('php://output', 'w');
                            fputcsv($file, $headers);
                            
                            // Instruction Row
                            fputcsv($file, [
                                'KOSONGKAN untuk Pegawai Baru, ISI untuk Update', 'NIPPAM (Otomatis jika kosong)', 'PIN Absensi (Wajib untuk mesin)', 'Wajib', 'male/female', 'Wajib', 'dd-mm-yyyy', 'Islam/Kristen/sdh', 'single/married/sdh', 'A/B/O/AB',
                                'Email Pribadi', 'Email Kantor', 'Hanya Angka', '16 Digit', '16 Digit', 'NPWP',
                                'No Rek', 'No BPJS TK', 'Aktif/Tidak', 'No BPJS Kes', 'Aktif/Tidak',
                                'Kelas 1/2/3', 'DPLK', 'DPLK', 'No Dapenma', 'Hanya Angka',
                                'Aktif/Tidak', 'Alamat Lengkap', 'Bag/Cab/Unit', 'Nama Unit/ID', 'Sub Bagian (Nama/ID)',
                                'Jabatan (Nama/ID)', 'Status (Nama/ID)', 'Pendidikan (Nama/ID)', 'Golongan (Nama/ID)', 'Tahun (0/2/4)',
                                'Jenis SK (Nama/ID)', 'Tgl Masuk (dd-mm-yy)', 'Tgl Pengangkatan (dd-mm-yy)', 'Cuti (Angka)',
                                'Tgl Kontrak Mulai', 'Tgl Kontrak Selesai', 'Tgl Golongan Mulai', 'Tgl Golongan Selesai', 'Tgl Gaji Berkala Mulai', 'Tgl Gaji Berkala Selesai'
                            ]);

                            // Get some real data for example
                            $edu = \App\Models\MasterEmployeeEducation::first()?->name ?? 'S1';
                            $pos = \App\Models\MasterEmployeePosition::first()?->name ?? 'Staff';
                            $grade = \App\Models\MasterEmployeeGrade::first()?->name ?? 'A1';
                            $status = \App\Models\MasterEmployeeStatusEmployment::first()?->name ?? 'Pegawai Tetap';
                            $dept = \App\Models\MasterDepartment::first();

                            fputcsv($file, [
                                '', 'NIP-202404-0001', '1001', 'Budi Santoso', 'male', 'Jakarta', '30-01-1990', 'Islam', 'married', 'O',
                                'budi@example.com', 'budi.pdam@example.com', '08123456789', '1234567890123456', '1234567890123456',
                                '12.345.678.9-012.345', '1234567890', '12345678901', 'Aktif', '0001234567890', 'Aktif',
                                'Kelas 1', '1234567890', '1234567890', '12345', '5000000', 'Aktif', 'Jl. Contoh No. 123',
                                $dept?->type ?? 'bagian', $dept?->name ?? 'Bagian Umum', 'Sub Bagian Umum', $pos, $status, $edu, $grade, '0',
                                'PKWTT', '01-01-2024', '01-07-2024', '12',
                                '01-01-2024', '31-12-2024', '01-01-2024', '01-01-2026', '01-01-2024', '01-01-2026'
                            ]);
                            fclose($file);
                        };

                        return response()->streamDownload($callback, 'template_manajemen_pegawai.csv');
                    }),

                Tables\Actions\Action::make('import_pegawai')
                    ->label('Import / Sync CSV')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->form(function() {
                        // Dynamically fetch options for help text
                        $grades = \App\Models\MasterEmployeeGrade::all()->map(fn($g) => "{$g->id}:{$g->name}")->implode(', ');
                        $edus = \App\Models\MasterEmployeeEducation::all()->map(fn($e) => "{$e->id}:{$e->name}")->implode(', ');
                        
                        return [
                            Forms\Components\FileUpload::make('csv_file')
                                ->label('File CSV Pegawai')
                                ->helperText(new \Illuminate\Support\HtmlString("
                                    <strong>Panduan Singkat:</strong><br>
                                    - Untuk EDIT: Isi kolom <code>id</code>. Untuk BARU: Kosongkan <code>id</code>.<br>
                                    - Data Master (Pendidikan, Jabatan, dll) bisa diisi <strong>Nama</strong> atau <strong>Angka ID</strong>.<br>
                                    - Contoh ID Golongan: <code>{$grades}</code><br>
                                    - Contoh ID Pendidikan: <code>{$edus}</code><br>
                                    - Tanggal wajib format <code>dd-mm-yyyy</code> (contoh: 31-01-1990).
                                "))
                                ->required()
                                ->acceptedFileTypes(['text/csv', 'application/csv']),
                        ];
                    })
                    ->action(function (array $data) {
                        set_time_limit(300); // 5 minutes
                        ini_set('memory_limit', '512M');

                        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($data['csv_file']);
                        
                        // Detect Delimiter (Comma or Semicolon)
                        $fileHandle = fopen($path, 'r');
                        $firstLine = fgets($fileHandle);
                        fclose($fileHandle);
                        $delimiter = (strpos($firstLine, ';') !== false && strpos($firstLine, ',') === false) ? ';' : ',';

                        $file = fopen($path, 'r');
                        $rows = [];
                        $tempRows = [];
                        
                        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
                            $tempRows[] = $row;
                        }
                        fclose($file);

                        // 1. Find the Header Row (Look for 'id', 'nippam', 'name')
                        $headerIndex = -1;
                        foreach ($tempRows as $idx => $row) {
                            // Strip BOM and non-breaking spaces
                            $rowNormalized = array_map(function($v) {
                                $v = str_replace("\xEF\xBB\xBF", '', $v); // Strip BOM
                                return strtolower(trim($v, " \t\n\r\0\x0B\u{A0}")); 
                            }, $row);

                            if (in_array('id', $rowNormalized) && in_array('nippam', $rowNormalized) && in_array('name', $rowNormalized)) {
                                $header = $rowNormalized; // USE NORMALIZED HEADER AS KEYS
                                $headerIndex = $idx;
                                break;
                            }
                        }

                        if ($headerIndex === -1) {
                            Notification::make()->title('Header CSV tidak ditemukan')->body('Pastikan kolom "id", "nippam", dan "name" tersedia.')->danger()->send();
                            return;
                        }

                        // 2. Identify Data Rows (Skip instruction row if present)
                        for ($i = $headerIndex + 1; $i < count($tempRows); $i++) {
                            $row = $tempRows[$i];
                            
                            // Skip if it looks like the Instruction Row (contains 'Wajib' or 'Otomatis' or 'dd-mm-yyyy')
                            $rowString = implode(' ', $row);
                            if (stripos($rowString, 'Wajib') !== false || stripos($rowString, 'Otomatis') !== false || stripos($rowString, 'dd-mm-yyyy') !== false) {
                                continue;
                            }

                            if (count($header) === count($row)) {
                                $rows[] = array_combine($header, $row);
                            }
                        }

                        if (empty($rows)) {
                            Notification::make()->title('File CSV kosong atau tidak valid')->danger()->send();
                            return;
                        }

                        $errors = [];
                        $processedData = [];

                        // Pre-load Master Data for Performance
                        $masterLookups = [
                            'positions' => \App\Models\MasterEmployeePosition::all()->mapWithKeys(fn($i) => [strtolower(trim($i->name)) => $i->id])->toArray(),
                            'status' => \App\Models\MasterEmployeeStatusEmployment::all()->mapWithKeys(fn($i) => [strtolower(trim($i->name)) => $i->id])->toArray(),
                            'education' => \App\Models\MasterEmployeeEducation::all()->mapWithKeys(fn($i) => [strtolower(trim($i->name)) => $i->id])->toArray(),
                            'grades' => \App\Models\MasterEmployeeGrade::all()->mapWithKeys(fn($i) => [strtolower(trim($i->name)) => $i->id])->toArray(),
                            'agreements' => \App\Models\MasterEmployeeAgreement::all()->mapWithKeys(fn($i) => [strtolower(trim($i->name)) => $i->id])->toArray(),
                            'sub_depts' => \App\Models\MasterSubDepartment::all()->mapWithKeys(fn($i) => [strtolower(trim($i->name)) => $i->id])->toArray(),
                            'mkg' => \App\Models\MasterEmployeeServiceGrade::all()->mapWithKeys(fn($i) => [(string)$i->service_grade => $i->id])->toArray(),
                            'units' => \App\Models\MasterDepartment::all()->mapWithKeys(fn($i) => [strtolower(trim($i->type . '|' . $i->name)) => $i->id])->toArray(),
                        ];

                        // Pre-load existing unique identifiers to avoid DB hits in loop
                        $uniques = [
                            'pin' => \App\Models\Employee::whereNotNull('pin')->pluck('id', 'pin')->toArray(),
                            'nippam' => \App\Models\Employee::whereNotNull('nippam')->pluck('id', 'nippam')->toArray(),
                            'id_number' => \App\Models\Employee::whereNotNull('id_number')->pluck('id', 'id_number')->toArray(),
                            'email' => \App\Models\Employee::whereNotNull('email')->pluck('id', 'email')->toArray(),
                        ];

                        // Phase 1: Validation
                        foreach ($rows as $index => $rowData) {
                            // Find line number in original file for better error reporting
                            $actualRowInFile = ($headerIndex + 1) + ($index + 1); 
                            $rowErrors = [];

                            if (empty($rowData['name'])) {
                                $rowErrors[] = "Nama wajib diisi";
                            }

                            // Smart Lookup Helper (Optimized with Pre-loaded data)
                            $smartLookup = function($lookupKey, $value, $label) use (&$rowErrors, $masterLookups) {
                                if (empty($value)) return null;
                                
                                $valClean = strtolower(trim($value));
                                
                                // Try ID match
                                if (is_numeric($value) && in_array($value, $masterLookups[$lookupKey])) {
                                    return (int)$value;
                                }
                                
                                // Try Name match
                                if (isset($masterLookups[$lookupKey][$valClean])) {
                                    return $masterLookups[$lookupKey][$valClean];
                                }

                                $rowErrors[] = "{$label} '{$value}' tidak ditemukan (Gunakan Nama atau ID yang valid)";
                                return null;
                            };

                            // Unit Lookup (Optimized)
                            $unitType = trim(strtolower($rowData['work_unit_type'] ?? ''));
                            $unitName = trim($rowData['work_unit_name'] ?? '');
                            $deptId = null; $bagianId = null; $cabangId = null; $unitId = null;

                            if ($unitType && $unitName) {
                                $unitKey = strtolower($unitType . '|' . $unitName);
                                if (isset($masterLookups['units'][$unitKey])) {
                                    $deptId = $masterLookups['units'][$unitKey];
                                } elseif (is_numeric($unitName)) {
                                    $deptId = (int)$unitName;
                                }

                                if (!$deptId) {
                                    $rowErrors[] = "Unit Kerja '{$unitName}' ({$unitType}) tidak ditemukan";
                                } else {
                                    if ($unitType === 'bagian') $bagianId = $deptId;
                                    elseif ($unitType === 'cabang') $cabangId = $deptId;
                                    elseif ($unitType === 'unit') $unitId = $deptId;
                                }
                            }

                            $subDeptId = $smartLookup('sub_depts', $rowData['sub_department_name'] ?? '', 'Sub Departemen');
                            $posId = $smartLookup('positions', $rowData['position_name'] ?? '', 'Jabatan');
                            $statusId = $smartLookup('status', $rowData['employment_status_name'] ?? '', 'Status');
                            $eduId = $smartLookup('education', $rowData['education_name'] ?? '', 'Pendidikan');
                            $gradeId = $smartLookup('grades', $rowData['grade_name'] ?? '', 'Golongan');
                            $agreementId = $smartLookup('agreements', $rowData['agreement_type_name'] ?? '', 'Perjanjian');

                            // Uniqueness Validation (Partial pre-load check to reduce queries)
                            $checkUnique = function($column, $value, $label) use ($rowData, &$rowErrors, $uniques) {
                                if (empty($value)) return;
                                
                                // For performance, we only do DB check if it's not in our small pre-load or if it's a field we didn't pre-load
                                if (isset($uniques[$column])) {
                                    if (isset($uniques[$column][$value]) && $uniques[$column][$value] != ($rowData['id'] ?? null)) {
                                        $rowErrors[] = "{$label} '{$value}' sudah terdaftar";
                                    }
                                    return;
                                }

                                // Fallback for other columns
                                $query = \App\Models\Employee::where($column, $value);
                                if (!empty($rowData['id'])) {
                                    $query->where('id', '!=', $rowData['id']);
                                }
                                if ($query->exists()) {
                                    $rowErrors[] = "{$label} '{$value}' sudah terdaftar";
                                }
                            };

                            $checkUnique('pin', $rowData['pin'] ?? '', 'PIN Absensi');
                            $checkUnique('nippam', $rowData['nippam'] ?? '', 'NIPPAM');
                            $checkUnique('id_number', $rowData['id_number'] ?? '', 'NIK');
                            $checkUnique('email', $rowData['email'] ?? '', 'Email');

                            // MKG Lookup
                            $mkgValue = trim($rowData['mkg_years'] ?? '');
                            $mkgId = null;
                            if ($mkgValue !== '') {
                                if (isset($masterLookups['mkg'][$mkgValue])) {
                                    $mkgId = $masterLookups['mkg'][$mkgValue];
                                } else {
                                    $rowErrors[] = "MKG '{$mkgValue}' tidak ditemukan";
                                }
                            }

                            if (!empty($rowErrors)) {
                                $displayName = ($rowData['name'] ?? null) ?: ($rowData['nippam'] ?? 'N/A');
                                $errors[] = "<strong>Baris {$actualRowInFile} ({$displayName}):</strong> " . implode(', ', $rowErrors);
                            }

                            $processedData[] = [
                                'row' => $rowData,
                                'ids' => [
                                    'dept_id' => $deptId, 'bagian_id' => $bagianId, 'cabang_id' => $cabangId, 'unit_id' => $unitId,
                                    'sub_dept_id' => $subDeptId, 'pos_id' => $posId, 'status_id' => $statusId,
                                    'edu_id' => $eduId, 'grade_id' => $gradeId, 'mkg_id' => $mkgId, 'agreement_id' => $agreementId,
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

                        $stats = ['created' => 0, 'updated' => 0];
                        // Phase 2: Processing (Transactional)
                        try {
                            \Illuminate\Support\Facades\DB::transaction(function() use ($processedData, &$stats) {
                                foreach ($processedData as $data) {
                                    $rowData = $data['row'];
                                    $ids = $data['ids'];

                                    $convertDate = function($dateStr) {
                                        if (empty($dateStr)) return null;
                                        $dateStr = trim($dateStr);
                                        // Try various common formats covers dash, slash, dot and 2/4 digit years
                                        $formats = [
                                            'd-m-Y', 'd/m/Y', 'd.m.Y', 
                                            'd-m-y', 'd/m/y', 'd.m.y',
                                            'j-n-Y', 'j/n/Y', 'j.n.Y', 
                                            'Y-m-d', 'Y/m/d', 'Y.m.d',
                                        ];
                                        foreach ($formats as $format) {
                                            try { 
                                                $d = \Carbon\Carbon::createFromFormat($format, $dateStr);
                                                if ($d) {
                                                    // Sanity check for 2-digit year misparsing (e.g. 24 -> 0024)
                                                    if ($d->year < 100) {
                                                        $d->year($d->year < 70 ? 2000 + $d->year : 1900 + $d->year);
                                                    }
                                                    return $d->format('Y-m-d');
                                                }
                                            } catch (\Exception $e) {}
                                        }
                                        
                                        // Final attempt: Smart parsing
                                        try {
                                            $d = \Carbon\Carbon::parse($dateStr);
                                            if ($d->year < 100) {
                                                $d->year($d->year < 70 ? 2000 + $d->year : 1900 + $d->year);
                                            }
                                            return $d->format('Y-m-d');
                                        } catch (\Exception $e) {
                                            return null;
                                        }
                                    };

                                    $isNew = empty($rowData['id']);
                                    if ($isNew) $stats['created']++; else $stats['updated']++;
                                    
                                    $employeeData = [
                                        'name' => $rowData['name'],
                                        'pin' => $rowData['pin'] ?? null,
                                        'gender' => $rowData['gender'] ?? 'male',
                                        'religion' => $rowData['religion'] ?? null,
                                        'place_birth' => $rowData['place_birth'] ?? null,
                                        'date_birth' => $convertDate($rowData['date_birth'] ?? ''),
                                        'marital_status' => $rowData['marital_status'] ?? 'single',
                                        'blood_type' => $rowData['blood_type'] ?? null,
                                        'email' => $rowData['email'] ?? null,
                                        'office_email' => $rowData['office_email'] ?? null,
                                        'phone_number' => $rowData['phone_number'] ?? null,
                                        'id_number' => $rowData['id_number'] ?? null,
                                        'familycard_number' => $rowData['familycard_number'] ?? null,
                                        'npwp_number' => $rowData['npwp_number'] ?? null,
                                        'bank_account_number' => $rowData['bank_account_number'] ?? null,
                                        'bpjs_tk_number' => $rowData['bpjs_tk_number'] ?? null,
                                        'bpjs_tk_status' => $rowData['bpjs_tk_status'] ?? 'Aktif',
                                        'bpjs_kes_number' => $rowData['bpjs_kes_number'] ?? null,
                                        'bpjs_kes_status' => $rowData['bpjs_kes_status'] ?? 'Aktif',
                                        'bpjs_kes_class' => $rowData['bpjs_kes_class'] ?? null,
                                        'rek_dplk_pribadi' => $rowData['rek_dplk_pribadi'] ?? null,
                                        'rek_dplk_bersama' => $rowData['rek_dplk_bersama'] ?? null,
                                        'dapenma_number' => $rowData['dapenma_number'] ?? null,
                                        'dapenma_phdp' => (isset($rowData['dapenma_phdp']) && $rowData['dapenma_phdp'] !== '') ? $rowData['dapenma_phdp'] : null,
                                        'dapenma_status' => $rowData['dapenma_status'] ?? 'Aktif',
                                        'address' => $rowData['address'] ?? null,
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
                                        'entry_date' => $convertDate($rowData['entry_date'] ?? ''),
                                        'probation_appointment_date' => $convertDate($rowData['probation_appointment_date'] ?? ''),
                                        'leave_balance' => $rowData['leave_balance'] ?? 12,
                                        'agreement_date_start' => $convertDate($rowData['agreement_date_start'] ?? ''),
                                        'agreement_date_end' => $convertDate($rowData['agreement_date_end'] ?? ''),
                                        'grade_date_start' => $convertDate($rowData['grade_date_start'] ?? ''),
                                        'grade_date_end' => $convertDate($rowData['grade_date_end'] ?? ''),
                                        'periodic_salary_date_start' => $convertDate($rowData['periodic_salary_date_start'] ?? ''),
                                        'periodic_salary_date_end' => $convertDate($rowData['periodic_salary_date_end'] ?? ''),
                                        'users_id' => auth()->id() ?? 1,
                                    ];

                                    if ($rowData['nippam']) {
                                        $employeeData['nippam'] = $rowData['nippam'];
                                    } elseif ($isNew) {
                                        $employeeData['nippam'] = \App\Models\Employee::generateNippam();
                                    }

                                    $employee = \App\Models\Employee::updateOrCreate(
                                        ['id' => $rowData['id'] ?: null],
                                        $employeeData
                                    );

                                    if ($isNew) {
                                        // 1. Create User
                                        $user = \App\Models\User::create([
                                            'name' => $employee->name,
                                            'email' => $employee->email ?: "pegawai_{$employee->id}@pdam.com",
                                            'password' => \Illuminate\Support\Facades\Hash::make('pdam891706'),
                                            'is_verified' => true,
                                        ]);
                                        $employee->update(['users_id' => $user->id]);

                                        // 2. Create Initial Appointment
                                        \App\Models\EmployeeAppointment::create([
                                            'employee_id' => $employee->id,
                                            'decision_letter_number' => 'SK-IMPORT-' . now()->format('YmdHis'),
                                            'appointment_date' => $employee->entry_date ?: now(),
                                            'new_employment_status_id' => $employee->employment_status_id ?: 1,
                                            'employee_grade_id' => $employee->basic_salary_id,
                                            'is_applied' => true,
                                            'applied_at' => now(),
                                            'users_id' => auth()->id() ?? 1,
                                        ]);
                                    }
                                }
                            });

                            Notification::make()
                                ->title('Proses Berhasil')
                                ->body("Berhasil: {$stats['created']} pegawai baru, {$stats['updated']} pegawai diperbarui.")
                                ->success()->send();
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Import Error: ' . $e->getMessage());
                            Notification::make()
                                ->title('Proses Gagal')
                                ->body('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),


            ])
            ->filters([
                Tables\Filters\SelectFilter::make('departments_id')
                    ->label('Bagian')
                    ->relationship('department', 'name')
                    ->label('Bagian')
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
                                            Forms\Components\TextInput::make('pin')
                                                ->label('PIN Absensi')
                                                ->unique(ignoreRecord: true)
                                                ->helperText('ID unik untuk mesin absensi')
                                                ->maxLength(50),
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
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Export Terpilih')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $headers = [
                                'id', 'nippam', 'pin', 'name', 'gender', 'place_birth', 'date_birth', 'religion', 'marital_status', 'blood_type',
                                'email', 'office_email', 'phone_number', 'id_number', 'familycard_number', 'npwp_number',
                                'bank_account_number', 'bpjs_tk_number', 'bpjs_tk_status', 'bpjs_kes_number', 'bpjs_kes_status',
                                'bpjs_kes_class', 'rek_dplk_pribadi', 'rek_dplk_bersama', 'dapenma_number', 'dapenma_phdp',
                                'dapenma_status', 'address', 'work_unit_type', 'work_unit_name', 'sub_department_name',
                                'position_name', 'employment_status_name', 'education_name', 'grade_name', 'mkg_years',
                                'agreement_type_name', 'entry_date', 'probation_appointment_date', 'leave_balance',
                                'agreement_date_start', 'agreement_date_end', 'grade_date_start', 'grade_date_end',
                                'periodic_salary_date_start', 'periodic_salary_date_end'
                            ];

                            $callback = function () use ($records, $headers) {
                                $file = fopen('php://output', 'w');
                                fputcsv($file, $headers);

                                // Add Instruction Row for consistency
                                fputcsv($file, [
                                    'KOSONGKAN untuk Pegawai Baru, ISI untuk Update', 'Otomatis', 'PIN Absensi', 'Wajib', 'male/female', 'Wajib', 'dd-mm-yyyy', 'Islam/Kristen/sdh', 'single/married/sdh', 'A/B/O/AB',
                                    'Email Pribadi', 'Email Kantor', 'Hanya Angka', '16 Digit', '16 Digit', 'NPWP',
                                    'No Rek', 'No BPJS TK', 'Aktif/Tidak', 'No BPJS Kes', 'Aktif/Tidak',
                                    'Kelas 1/2/3', 'DPLK', 'DPLK', 'No Dapenma', 'Hanya Angka',
                                    'Aktif/Tidak', 'Alamat Lengkap', 'bagian/cabang/unit', 'Nama Bagian/ID', 'Nama/ID',
                                    'Nama/ID', 'Nama/ID', 'Nama/ID', 'Nama/ID', 'Tahun (0/2/4)',
                                    'Nama/ID', 'dd-mm-yyyy', 'dd-mm-yyyy', 'Angka',
                                    'dd-mm-yyyy', 'dd-mm-yyyy', 'dd-mm-yyyy', 'dd-mm-yyyy', 'dd-mm-yyyy', 'dd-mm-yyyy'
                                ]);

                                foreach ($records as $record) {
                                    $record->load(['position', 'employmentStatus', 'education', 'grade', 'serviceGrade', 'agreement', 'department', 'subDepartment', 'bagian', 'cabang', 'unit']);
                                    
                                    // Determine work unit type and name
                                    $unitType = ''; $unitName = '';
                                    if ($record->bagian_id) { $unitType = 'bagian'; $unitName = $record->bagian->name ?? ''; }
                                    elseif ($record->cabang_id) { $unitType = 'cabang'; $unitName = $record->cabang->name ?? ''; }
                                    elseif ($record->unit_id) { $unitType = 'unit'; $unitName = $record->unit->name ?? ''; }
                                    elseif ($record->departments_id) { $unitType = 'bagian'; $unitName = $record->department->name ?? ''; }

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
                                        $record->subDepartment->name ?? '',
                                        $record->position->name ?? '',
                                        $record->employmentStatus->name ?? '',
                                        $record->education->name ?? '',
                                        $record->grade->name ?? '',
                                        $record->serviceGrade->service_grade ?? '',
                                        $record->agreement->name ?? '',
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
            ->defaultSort('created_at', 'desc');
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
                                        ->disk('public')
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
                                                        ->hidden(fn (Employee $record) => $record->families->isEmpty()),
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
                                                        ->hidden(fn (Employee $record) => $record->employeeAgreements->isEmpty()),
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
                                                        ->hiddenLabel()
                                                        ->hidden(fn (Employee $record) => $record->mutations->isEmpty()),
                                                ])->compact()->collapsible()->collapsed(),

                                            Infolists\Components\Section::make('Histori Pergerakan Karir')
                                                ->icon('heroicon-o-briefcase')
                                                ->schema([
                                                    Infolists\Components\ViewEntry::make('career_movement_data')
                                                        ->view('filament.components.career-movement-table')
                                                        ->hiddenLabel()
                                                        ->hidden(fn (Employee $record) => $record->careerMovements->isEmpty()),
                                                ])->compact()->collapsible()->collapsed(),

                                            Infolists\Components\Section::make('Histori Kenaikan Golongan / Promosi')
                                                ->icon('heroicon-o-chevron-double-up')
                                                ->schema([
                                                    Infolists\Components\ViewEntry::make('grade_promotion_data')
                                                        ->view('filament.components.promotion-history-table')
                                                        ->hiddenLabel()
                                                        ->hidden(fn (Employee $record) => $record->promotions->isEmpty()),
                                                ])->compact()->collapsible()->collapsed(),

                                            Infolists\Components\Section::make('Histori Pengangkatan')
                                                ->icon('heroicon-o-check-badge')
                                                ->schema([
                                                    Infolists\Components\ViewEntry::make('appointment_data')
                                                        ->view('filament.components.appointment-history-table')
                                                        ->hiddenLabel()
                                                        ->hidden(fn (Employee $record) => $record->appointments->isEmpty()),
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
            ])
            ->with(['position', 'employmentStatus', 'grade', 'serviceGrade', 'department', 'subDepartment', 'bagian', 'cabang', 'unit', 'attendanceMachineLogs.machine.officeLocation']);
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
