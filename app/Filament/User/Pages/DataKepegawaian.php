<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use Filament\Infolists\Infolist;
use Filament\Infolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use App\Models\AttendanceSchedule;
use App\Models\EmployeeAttendanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DataKepegawaian extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static string $view = 'filament.user.pages.data-kepegawaian';
    protected static ?string $navigationLabel = 'Data Kepegawaian';
    protected static ?string $title = 'Data Kepegawaian';
    protected static ?string $slug = 'data-kepegawaian';
    protected static ?string $navigationGroup = 'Utama';
    protected static ?int $navigationSort = 1;
    
    public ?Employee $employee = null;
    
    // Stats properties
    public int $monthlyPresence = 0;
    public int $monthlyAbsence = 0;
    public int $monthlyLate = 0;
    public int $monthlyPermit = 0;
    public int $monthlyOvertimeCount = 0;
    public string $monthlyOvertimeHours = '0j 0m';
    
    public function mount(): void
    {
        $user = Auth::user();
        $this->employee = Employee::with([
            'families.masterFamily',
            'employeeAgreements.masterAgreement',
            'attendanceRecords',
            'attendanceMachineLogs.machine.officeLocation',
            'employeePermissions.permission',
            'mutations.oldPosition',
            'mutations.newPosition',
            'promotions.oldSalaryGrade',
            'promotions.newSalaryGrade',
            'salaries',
            'trainings',
            'assignmentLetters',
            'businessTravelLetters',
            'dailyReports',
            'position',
            'employmentStatus',
            'grade',
            'department',
        ])
        ->where('users_id', $user->id)
        ->first();

        if (!$this->employee) {
            return;
        }

        $this->calculateMonthlyStats();
    }

    protected function calculateMonthlyStats(): void
    {
        $now = now();
        $stats = \App\Services\AttendanceService::getMonthlyStatsForEmployee(
            $this->employee,
            $now->month,
            $now->year
        );

        $this->monthlyPresence = $stats['presence'];
        $this->monthlyLate = $stats['late'];
        $this->monthlyPermit = $stats['permit'];
        $this->monthlyAbsence = $stats['absence'];
        $this->monthlyOvertimeCount = $stats['overtime_count'];
        $this->monthlyOvertimeHours = $stats['overtime_hours'];
    }

    public function employeeInfolist(Infolist $infolist): Infolist
    {
        if (!$this->employee) {
            return $infolist->schema([
                Infolists\Components\Section::make('Peringatan')
                    ->schema([
                        Infolists\Components\TextEntry::make('warning')
                            ->state('Data kepegawaian Anda belum terhubung. Silakan hubungi Admin HRD untuk menautkan akun Anda dengan data pegawai.')
                            ->color('danger')
                            ->weight('bold'),
                    ]),
            ]);
        }

        return $infolist
            ->record($this->employee)
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

                Infolists\Components\Section::make('Statistik Kehadiran Bulan Ini')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('monthly_presence')
                                    ->label('Total Kehadiran')
                                    ->state($this->monthlyPresence . ' Hari')
                                    ->color('success')
                                    ->icon('heroicon-m-check-circle'),
                                Infolists\Components\TextEntry::make('monthly_absence')
                                    ->label('Ketidak Hadiran')
                                    ->state($this->monthlyAbsence . ' Hari')
                                    ->color('danger')
                                    ->icon('heroicon-m-x-circle'),
                                Infolists\Components\TextEntry::make('monthly_late')
                                    ->label('Keterlambatan')
                                    ->state($this->monthlyLate . ' Kali')
                                    ->color('warning')
                                    ->icon('heroicon-m-clock'),
                                Infolists\Components\TextEntry::make('monthly_permit')
                                    ->label('Izin & Cuti')
                                    ->state($this->monthlyPermit . ' Hari')
                                    ->color('info')
                                    ->icon('heroicon-m-calendar'),
                            ]),
                        
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('overtime_count')
                                    ->label('Total Overtime')
                                    ->state($this->monthlyOvertimeCount . ' Sesi')
                                    ->color('primary')
                                    ->icon('heroicon-m-fire'),
                                Infolists\Components\TextEntry::make('overtime_hours')
                                    ->label('Jumlah Jam Overtime')
                                    ->state($this->monthlyOvertimeHours)
                                    ->color('primary')
                                    ->icon('heroicon-m-variable'),
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
                                Infolists\Components\TextEntry::make('bank_account_number')
                                    ->label('No. Rekening Bank'),
                                Infolists\Components\TextEntry::make('npwp_number')
                                    ->label('NPWP'),
                            ])->columnSpan(1),
                    ]),

                Infolists\Components\Section::make('Riwayat Perjalanan Karir')
                    ->schema([
                        Infolists\Components\ViewEntry::make('recruitment_progress')
                            ->view('filament.components.recruitment-progress-bar')
                            ->columnSpanFull()
                            ->hiddenLabel(),
                    ])->collapsible(),

                Infolists\Components\Section::make('Riwayat Kehadiran')
                    ->schema([
                        Infolists\Components\ViewEntry::make('attendance_data')
                            ->view('filament.components.attendance-history-table')
                            ->columnSpanFull()
                            ->hiddenLabel(),
                    ])->collapsible()->collapsed(),

                Infolists\Components\Section::make('Riwayat Izin & Cuti')
                    ->schema([
                        Infolists\Components\ViewEntry::make('permission_data')
                            ->view('filament.components.permission-history-table')
                            ->columnSpanFull()
                            ->hiddenLabel(),
                    ])->collapsible()->collapsed(),

                Infolists\Components\Section::make('Data Keluarga')
                    ->schema([
                        Infolists\Components\ViewEntry::make('family_data')
                            ->view('filament.components.family-data-table')
                            ->columnSpanFull()
                            ->hiddenLabel(),
                    ])->collapsible()->collapsed()
                    ->hidden(fn (Employee $record) => $record->families->isEmpty()),

                Infolists\Components\Section::make('Riwayat Kontrak')
                    ->schema([
                        Infolists\Components\ViewEntry::make('agreement_data')
                            ->view('filament.components.agreement-data-table')
                            ->columnSpanFull()
                            ->hiddenLabel(),
                    ])->collapsible()->collapsed()
                    ->hidden(fn (Employee $record) => $record->employeeAgreements->isEmpty()),

                Infolists\Components\Section::make('Riwayat Mutasi')
                    ->schema([
                        Infolists\Components\ViewEntry::make('mutation_data')
                            ->view('filament.components.mutation-data-table')
                            ->columnSpanFull()
                            ->hiddenLabel(),
                    ])->collapsible()->collapsed()
                    ->hidden(fn (Employee $record) => $record->mutations->isEmpty()),

                Infolists\Components\Section::make('Riwayat Karir')
                    ->schema([
                        Infolists\Components\ViewEntry::make('career_movement_data')
                            ->view('filament.components.career-movement-table')
                            ->columnSpanFull()
                            ->hiddenLabel(),
                    ])->collapsible()->collapsed()
                    ->hidden(fn (Employee $record) => $record->careerMovements->isEmpty()),

                Infolists\Components\Section::make('Riwayat Kenaikan Tahunan')
                    ->schema([
                        Infolists\Components\ViewEntry::make('grade_promotion_data')
                            ->view('filament.components.promotion-history-table')
                            ->columnSpanFull()
                            ->hiddenLabel(),
                    ])->collapsible()->collapsed()
                    ->hidden(fn (Employee $record) => $record->promotions->isEmpty()),

                Infolists\Components\Section::make('Riwayat SK')
                    ->schema([
                        Infolists\Components\ViewEntry::make('appointment_data')
                            ->view('filament.components.appointment-history-table')
                            ->columnSpanFull()
                            ->hiddenLabel(),
                    ])->collapsible()->collapsed()
                    ->hidden(fn (Employee $record) => $record->appointments->isEmpty()),
            ]);
    }
}
