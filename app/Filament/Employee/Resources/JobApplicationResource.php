<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\JobApplicationResource\Pages;
use App\Models\JobApplication;
use App\Models\JobApplicationArchive;
use App\Models\EmployeeAgreement;
use App\Models\MasterDepartment;
use App\Models\MasterEmployeePosition;
use App\Models\MasterEmployeeEducation;
use App\Models\MasterSubDepartment;
use App\Models\MasterEmployeeAgreement;
use App\Models\MasterEmployeeStatusEmployment;
use App\Models\MasterEmployeeGrade;
use App\Models\InterviewProcess;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;

class JobApplicationResource extends Resource
{
    protected static ?string $model = JobApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Lamaran Kerja';

    protected static ?string $modelLabel = 'Lamaran Kerja';

    protected static ?string $pluralModelLabel = 'Lamaran Kerja';

    protected static ?string $navigationGroup = 'Rekrutmen & Seleksi';

    protected static ?int $navigationSort = 101;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pribadi')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('id_number')
                                    ->label('NIK (KTP)')
                                    ->required()
                                    ->numeric()
                                    ->minLength(16)
                                    ->maxLength(16)
                                    ->unique(ignoreRecord: true),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('place_birth')
                                    ->label('Tempat Lahir')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('date_birth')
                                    ->label('Tanggal Lahir')
                                    ->required(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('gender')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'male' => 'Laki-laki',
                                        'female' => 'Perempuan',
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('marital_status')
                                    ->label('Status Pernikahan')
                                    ->options([
                                        'single' => 'Belum Menikah',
                                        'married' => 'Menikah',
                                        'divorced' => 'Cerai',
                                        'widowed' => 'Janda/Duda',
                                    ])
                                    ->required(),
                            ]),

                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->required()
                            ->rows(3),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('phone_number')
                                    ->label('No. Telepon')
                                    ->tel()
                                    ->required()
                                    ->numeric()
                                    ->maxLength(20)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                            ]),
                    ]),

                Forms\Components\Section::make('Dokumen Lamaran')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto Pas')
                            ->image()
                            ->imageEditor()
                            ->directory('job-applications/photos')
                            ->visibility('public')
                            ->required(),

                        Forms\Components\FileUpload::make('documents')
                            ->label('Unggah Dokumen (CV, Ijazah, KTP, dll)')
                            ->multiple()
                            ->directory('job-applications/documents')
                            ->visibility('public')
                            ->openable()
                            ->downloadable()
                            ->reorderable()
                            ->appendFiles()
                            ->maxSize(10240),
                    ]),

                Forms\Components\Section::make('Posisi yang Dilamar')
                    ->schema([
                        Forms\Components\Select::make('applied_department_id')
                            ->label('Bagian')
                            ->options(MasterDepartment::pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn($state, Forms\Set $set) => $set('applied_sub_department_id', null)),

                        Forms\Components\Select::make('applied_sub_department_id')
                            ->label('Sub Bagian')
                            ->options(
                                fn(Forms\Get $get): array =>
                                MasterSubDepartment::where('departments_id', $get('applied_department_id'))
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->disabled(fn(Forms\Get $get): bool => !$get('applied_department_id')),

                        Forms\Components\Select::make('applied_position_id')
                            ->label('Posisi')
                            ->options(MasterEmployeePosition::pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                    ]),

                Forms\Components\Section::make('Pendidikan')
                    ->schema([
                        Forms\Components\Select::make('education_level_id')
                            ->label('Tingkat Pendidikan')
                            ->options(MasterEmployeeEducation::pluck('name', 'id'))
                            ->required(),

                        Forms\Components\TextInput::make('education_institution')
                            ->label('Nama Institusi')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('education_major')
                                    ->label('Jurusan')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('education_graduation_year')
                                    ->label('Tahun Lulus')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1980)
                                    ->maxValue(date('Y')),
                            ]),

                        Forms\Components\TextInput::make('education_gpa')
                            ->label('IPK/Nilai')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(5),
                    ]),

                Forms\Components\Section::make('Pengalaman Kerja Terakhir')
                    ->schema([
                        Forms\Components\TextInput::make('last_company_name')
                            ->label('Nama Perusahaan')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('last_position')
                            ->label('Posisi')
                            ->maxLength(255),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('last_work_start_date')
                                    ->label('Tanggal Mulai'),
                                Forms\Components\DatePicker::make('last_work_end_date')
                                    ->label('Tanggal Berakhir'),
                            ]),

                        Forms\Components\Textarea::make('last_work_description')
                            ->label('Deskripsi Pekerjaan')
                            ->rows(3),

                        Forms\Components\TextInput::make('last_salary')
                            ->label('Gaji Terakhir')
                            ->numeric()
                            ->prefix('Rp'),
                    ]),

                Forms\Components\Section::make('Ekspektasi')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('expected_salary')
                                    ->label('Ekspektasi Gaji')
                                    ->numeric()
                                    ->prefix('Rp'),
                                Forms\Components\DatePicker::make('available_start_date')
                                    ->label('Bisa Mulai Kerja'),
                            ]),
                    ]),

                Forms\Components\Section::make('Referensi')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('reference_name')
                                    ->label('Nama Referensi')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('reference_phone')
                                    ->label('No. Telepon Referensi')
                                    ->tel()
                                    ->maxLength(20),
                            ]),

                        Forms\Components\TextInput::make('reference_relation')
                            ->label('Hubungan dengan Referensi')
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Status & Catatan')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'submitted' => 'Baru Dikirim',
                                'reviewed' => 'Sedang Direview',
                                'interview_scheduled' => 'Dijadwalkan Interview',
                                'interviewed' => 'Sudah Interview',
                                'accepted' => 'Diterima',
                                'rejected' => 'Ditolak',
                                'withdrawn' => 'Dibatalkan',
                            ])
                            ->default('submitted')
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan HR')
                            ->rows(3),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto Pas')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Pelamar')
                    ->description(fn (JobApplication $record): string => $record->application_number)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('appliedPosition.name')
                    ->label('Posisi/Dept')
                    ->description(fn (JobApplication $record): string => $record->appliedDepartment->name ?? '')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'submitted',
                        'warning' => 'reviewed',
                        'info' => 'interview_scheduled',
                        'primary' => 'interviewed',
                        'success' => 'accepted',
                        'danger' => 'rejected',
                        'gray' => 'withdrawn',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'submitted' => 'Baru Dikirim',
                        'reviewed' => 'Sedang Direview',
                        'interview_scheduled' => 'Dijadwalkan Interview',
                        'interviewed' => 'Sudah Interview',
                        'accepted' => 'Diterima',
                        'rejected' => 'Ditolak',
                        'withdrawn' => 'Dibatalkan',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Tgl Melamar')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Kontak')
                    ->description(fn (JobApplication $record): string => $record->phone_number)
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'submitted' => 'Baru Dikirim',
                        'reviewed' => 'Sedang Direview',
                        'interview_scheduled' => 'Dijadwalkan Interview',
                        'interviewed' => 'Sudah Interview',
                        'accepted' => 'Diterima',
                        'rejected' => 'Ditolak',
                        'withdrawn' => 'Dibatalkan',
                    ]),

                Tables\Filters\SelectFilter::make('applied_department_id')
                    ->label('Departemen')
                    ->relationship('appliedDepartment', 'name'),

                Tables\Filters\SelectFilter::make('applied_position_id')
                    ->label('Posisi')
                    ->relationship('appliedPosition', 'name'),

                Tables\Filters\Filter::make('submitted_at')
                    ->form([
                        Forms\Components\DatePicker::make('submitted_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('submitted_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['submitted_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('submitted_at', '>=', $date),
                            )
                            ->when(
                                $data['submitted_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('submitted_at', '<=', $date),
                            );
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Profil')
                        ->icon('heroicon-o-eye')
                        ->color('info'),
                    Tables\Actions\Action::make('print')
                        ->label('Cetak Profil')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn(JobApplication $record): string => route('job-applications.print', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\EditAction::make()
                        ->visible(
                            fn(?JobApplication $record): bool =>
                            $record && !in_array($record->status, ['accepted', 'rejected'])
                        ),

                    Tables\Actions\Action::make('schedule_interview')
                        ->label('Jadwalkan Interview')
                        ->icon('heroicon-o-calendar')
                        ->color('danger')
                        ->visible(
                            fn(?JobApplication $record): bool =>
                            $record && in_array($record->status, ['submitted', 'reviewed'])
                        )
                        ->form([
                            Forms\Components\DateTimePicker::make('interview_datetime')
                                ->label('Tanggal & Waktu Interview')
                                ->required()
                                ->default(now()->addDays(1)->setHour(9)->setMinute(0)),
                            Forms\Components\Select::make('interview_location')
                                ->label('Lokasi Interview')
                                ->options([
                                    'Kantor Pusat' => 'Kantor Pusat',
                                    'Kantor Cabang' => 'Kantor Cabang',
                                    'Online (Zoom/Meet)' => 'Online (Zoom/Meet)',
                                ])
                                ->required(),
                            Forms\Components\TextInput::make('interviewer_name')
                                ->label('Nama Pewawancara')
                                ->placeholder('Contoh: Nama HRD / Manajer')
                                ->required(),
                            Forms\Components\Textarea::make('interview_notes')
                                ->label('Catatan / Instruksi')
                                ->rows(3),
                        ])
                        ->action(function (?JobApplication $record, array $data): void {
                            if (!$record) return;

                            $record->update([
                                'status' => 'interview_scheduled',
                                'interview_schedule' => [
                                    'datetime' => $data['interview_datetime'],
                                    'location' => $data['interview_location'],
                                    'notes' => $data['interview_notes'],
                                    'interviewer_name' => $data['interviewer_name'],
                                    'scheduled_by' => auth()->id() ?? 0,
                                    'scheduled_at' => now(),
                                ],
                            ]);

                            // Create InterviewProcess record
                            InterviewProcess::create([
                                'job_application_id' => $record->id,
                                'interview_stage' => 1,
                                'interview_type' => 'HR Interview',
                                'interview_date' => Carbon::parse($data['interview_datetime'])->toDateString(),
                                'interview_time' => Carbon::parse($data['interview_datetime'])->format('H:i'),
                                'interview_location' => $data['interview_location'],
                                'interviewer_name' => $data['interviewer_name'],
                                'interviewer_id' => auth()->id(), // System user
                                'notes' => $data['interview_notes'],
                                'status' => 'scheduled',
                                'result' => 'pending',
                                'users_id' => auth()->id(),
                            ]);

                            Notification::make()
                                ->title('Interview berhasil dijadwalkan')
                                ->body('Data sudah diteruskan ke Proses Interview')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('mark_interviewed')
                        ->label('Tandai Sudah Interview')
                        ->icon('heroicon-o-check-circle')
                        ->color('primary')
                        ->visible(
                            fn(?JobApplication $record): bool =>
                            $record && $record->status === 'interview_scheduled'
                        )
                        ->form([
                            Forms\Components\TextInput::make('technical_score')
                                ->label('Nilai Teknis')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%'),
                            Forms\Components\TextInput::make('soft_skills_score')
                                ->label('Nilai Soft Skills')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%'),
                            Forms\Components\TextInput::make('overall_score')
                                ->label('Nilai Keseluruhan')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%'),
                            Forms\Components\Textarea::make('interviewer_notes')
                                ->label('Catatan Interviewer')
                                ->rows(3),
                            Forms\Components\Select::make('recommendation')
                                ->label('Rekomendasi')
                                ->options([
                                    'highly_recommended' => 'Sangat Direkomendasikan',
                                    'recommended' => 'Direkomendasikan',
                                    'consider' => 'Perlu Pertimbangan',
                                    'not_recommended' => 'Tidak Direkomendasikan',
                                ])
                                ->required(),
                        ])
                        ->action(function (?JobApplication $record, array $data): void {
                            if (!$record) return;

                            $record->update([
                                'status' => 'interviewed',
                                'interview_at' => now(),
                                'interview_results' => $data,
                            ]);

                            Notification::make()
                                ->title('Status interview berhasil diupdate')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('process_decision')
                        ->label('Proses Keputusan')
                        ->icon('heroicon-o-check-circle')
                        ->color('warning')
                        ->visible(
                            fn(?JobApplication $record): bool =>
                            $record && in_array($record->status, ['interviewed'])
                        )
                        ->form([
                            Forms\Components\Select::make('decision')
                                ->label('Keputusan')
                                ->options([
                                    'accepted' => 'Diterima',
                                    'rejected' => 'Ditolak',
                                ])
                                ->required()
                                ->live(),

                            Forms\Components\Textarea::make('decision_reason')
                                ->label('Alasan Keputusan')
                                ->required()
                                ->rows(3),

                            // Form untuk data kontrak jika diterima
                            Forms\Components\Section::make('Data Kontrak (Jika Diterima)')
                                ->schema([
                                    Forms\Components\Select::make('proposed_agreement_type_id')
                                        ->label('Jenis Kontrak')
                                        ->options(MasterEmployeeAgreement::pluck('name', 'id'))
                                        ->required(),

                                    Forms\Components\Select::make('proposed_employment_status_id')
                                        ->label('Status Kepegawaian')
                                        ->options(MasterEmployeeStatusEmployment::where('id', '!=', 6)->pluck('name', 'id'))
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            if ($state == 1 || $state == 2) { // THL atau Magang
                                                $set('proposed_salary', 0);
                                                $set('proposed_grade_id', null);
                                            } elseif ($state == 3) { // Kontrak
                                                $set('proposed_salary', 2500000); // Default UMR
                                                $set('proposed_grade_id', null);
                                            } else {
                                                $set('proposed_salary', null);
                                            }
                                        }),

                                    Forms\Components\Select::make('proposed_grade_id')
                                        ->label('Golongan')
                                        ->options(MasterEmployeeGrade::all()->pluck('name', 'id'))
                                        ->required(fn(Forms\Get $get): bool => in_array($get('proposed_employment_status_id'), [4, 5]))
                                        ->visible(fn(Forms\Get $get): bool => in_array($get('proposed_employment_status_id'), [4, 5]))
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            if ($state) {
                                                $grade = MasterEmployeeGrade::find($state);
                                                if ($grade && $grade->basic_salary > 0) {
                                                    $set('proposed_salary', $grade->basic_salary);
                                                }
                                            }
                                        })
                                        ->helperText('Pilih golongan untuk auto-fill gaji'),

                                    Forms\Components\TextInput::make('proposed_salary')
                                        ->label('Gaji Pokok')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->required(fn(Forms\Get $get): bool => !in_array($get('proposed_employment_status_id'), [1, 2]))
                                        ->visible(fn(Forms\Get $get): bool => !in_array($get('proposed_employment_status_id'), [1, 2]))
                                        ->step(100000)
                                        ->helperText('Gaji akan terisi otomatis berdasarkan status atau golongan')
                                        ->live()
                                        ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                                        ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace(['.', ','], '', $state) : null),

                                    Forms\Components\DatePicker::make('proposed_start_date')
                                        ->label('Tanggal Mulai Kerja')
                                        ->required(),
                                ])
                                ->visible(fn(Forms\Get $get): bool => $get('decision') === 'accepted'),
                        ])
                        ->action(function (?JobApplication $record, array $data): void {
                            if (!$record) return;

                            // Update status aplikasi
                            $record->update([
                                'status' => $data['decision'],
                                'decision_at' => now(),
                            ]);

                            // Buat archive
                            $archive = JobApplicationArchive::createFromJobApplication($record, $data);

                            // Jika diterima, buat kontrak otomatis dan record pegawai
                            if ($data['decision'] === 'accepted') {
                                $agreement = EmployeeAgreement::createFromJobApplication($archive);

                                // CREATE OR UPDATE EMPLOYEE RECORD
                                $employee = Employee::updateOrCreate(
                                    ['email' => $record->email],
                                    [
                                        'name' => $record->name,
                                        'phone_number' => $record->phone_number,
                                        'id_number' => $record->id_number,
                                        'image' => $record->photo, // Fulfill photo sync request
                                        'place_birth' => $record->place_birth,
                                        'date_birth' => $record->date_birth,
                                        'marital_status' => $record->marital_status,
                                        'gender' => $record->gender,
                                        'address' => $record->address,
                                        'employee_education_id' => $record->education_level_id,
                                        'employee_position_id' => $record->applied_position_id,
                                        'departments_id' => $record->applied_department_id,
                                        'sub_department_id' => $record->applied_sub_department_id,
                                        'entry_date' => $data['proposed_start_date'],
                                        'employment_status_id' => $data['proposed_employment_status_id'],
                                        'master_employee_agreement_id' => $data['proposed_agreement_type_id'],
                                        'basic_salary_id' => $data['proposed_grade_id'] ?? null,
                                    ]
                                );

                                Notification::make()
                                    ->title('Pelamar diterima, kontrak & data pegawai berhasil dibuat')
                                    ->body("Nomor NIK: {$employee->nippam}")
                                    ->success()
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('view_employee')
                                            ->label('Lihat Pegawai')
                                            ->url(route('filament.employee.resources.employees.view', ['record' => $employee->id]))
                                            ->button(),
                                    ])
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Keputusan berhasil diproses')
                                    ->body('Lamaran telah ditolak dan diarsipkan')
                                    ->success()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('archive')
                        ->label('Arsipkan')
                        ->icon('heroicon-o-archive-box')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Arsipkan Lamaran')
                        ->modalDescription('Apakah Anda yakin ingin mengarsipkan lamaran ini? Data akan dipindahkan ke Arsip Lamaran.')
                        ->form([
                            Forms\Components\Textarea::make('decision_reason')
                                ->label('Alasan Pengarsipan')
                                ->required()
                                ->rows(3),
                        ])
                        ->visible(
                            fn(?JobApplication $record): bool =>
                            $record && !in_array($record->status, ['accepted', 'rejected', 'archived'])
                        )
                        ->action(function (?JobApplication $record, array $data): void {
                            if (!$record) return;

                            // Create Archive Record
                            JobApplicationArchive::createFromJobApplication($record, [
                                'decision' => 'archived',
                                'decision_reason' => $data['decision_reason'],
                                'decision_date' => now()->toDateString(),
                            ]);

                            // Update Status and Soft Delete
                            $record->update(['status' => 'archived']);
                            $record->delete();

                            Notification::make()
                                ->title('Lamaran berhasil diarsipkan')
                                ->success()
                                ->send();
                        }),
                      
                ])
                    ->label('Action')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('submitted_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Header Card
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\ImageEntry::make('photo')
                                    ->hiddenLabel()
                                    ->circular()
                                    ->height(120)
                                    ->extraImgAttributes(['class' => 'shadow-lg border-2 border-primary-500']),
                                Infolists\Components\Grid::make(1)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Nama Lengkap')
                                            ->weight('bold')
                                            ->size('lg'),
                                        Infolists\Components\TextEntry::make('application_number')
                                            ->label('No. Lamaran')
                                            ->color('gray')
                                            ->copyable(),
                                    ])->columnSpan(2),
                                Infolists\Components\Grid::make(1)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('status')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'submitted' => 'gray',
                                                'reviewed' => 'warning',
                                                'interview_scheduled' => 'info',
                                                'interviewed' => 'primary',
                                                'accepted' => 'success',
                                                'rejected' => 'danger',
                                                'withdrawn' => 'gray',
                                                default => 'gray',
                                            }),
                                        Infolists\Components\TextEntry::make('submitted_at')
                                            ->label('Tanggal Melamar')
                                            ->dateTime('d F Y')
                                            ->color('gray'),
                                    ])->columnSpan(1),
                            ]),
                    ])->columnSpanFull(),

                // Main Info Grid
                Infolists\Components\Grid::make(3)
                    ->schema([
                        // Column 1: Data Pribadi
                        Infolists\Components\Section::make('Data Pribadi')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Infolists\Components\TextEntry::make('id_number')->label('NIK (KTP)'),
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('gender_label')->label('J. Kelamin'),
                                        Infolists\Components\TextEntry::make('marital_status_label')->label('Status Sipil'),
                                    ]),
                                Infolists\Components\TextEntry::make('birth_info')
                                    ->label('Tempat, Tgl Lahir')
                                    ->state(fn($record) => "{$record->place_birth}, " . $record->date_birth->format('d/m/Y')),
                                Infolists\Components\TextEntry::make('email')->icon('heroicon-m-envelope'),
                                Infolists\Components\TextEntry::make('phone_number')->icon('heroicon-m-phone'),
                                Infolists\Components\TextEntry::make('address')->label('Alamat'),
                            ])->columnSpan(1),

                        // Column 2: Pendidikan & Target
                        Infolists\Components\Section::make('Pendidikan & Posisi')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Infolists\Components\TextEntry::make('appliedPosition.name')
                                    ->label('Posisi Dilamar')
                                    ->weight('bold')
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('appliedDepartment.name')->label('Bagian'),
                                Infolists\Components\TextEntry::make('education_summary')
                                    ->label('Pendidikan Terakhir')
                                    ->state(fn($record) => "{$record->educationLevel->name} - {$record->education_institution}"),
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('education_major')->label('Jurusan'),
                                        Infolists\Components\TextEntry::make('education_gpa')->label('IPK/Nilai'),
                                    ]),
                                Infolists\Components\TextEntry::make('expected_salary')->label('Ekspektasi Gaji')->money('IDR'),
                                Infolists\Components\TextEntry::make('available_start_date')->label('Tersedia Mulai')->date('d F Y'),
                            ])->columnSpan(1),

                        // Column 3: Pengalaman & Lampiran
                        Infolists\Components\Grid::make(1)
                            ->schema([
                                Infolists\Components\Section::make('Pengalaman Terakhir')
                                    ->icon('heroicon-o-briefcase')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('last_company_name')->label('Perusahaan'),
                                        Infolists\Components\TextEntry::make('last_position')->label('Jabatan'),
                                        Infolists\Components\TextEntry::make('last_salary')->label('Gaji Terakhir')->money('IDR'),
                                    ])->collapsed(),

                                Infolists\Components\Section::make('Lampiran Dokumen')
                                    ->icon('heroicon-o-paper-clip')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('documents')
                                            ->hiddenLabel()
                                            ->schema([
                                                Infolists\Components\TextEntry::make('')
                                                    ->hiddenLabel()
                                                    ->formatStateUsing(fn ($state) => basename($state))
                                                    ->url(fn ($state) => asset('storage/' . $state), true)
                                                    ->color('primary')
                                                    ->icon('heroicon-o-document-arrow-down')
                                                    ->size('sm'),
                                            ])
                                            ->grid(1),
                                    ]),
                                
                                Infolists\Components\Section::make('Referensi')
                                    ->icon('heroicon-o-identification')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('reference_name')->label('Nama'),
                                        Infolists\Components\TextEntry::make('reference_phone')->label('Telepon'),
                                    ])->collapsed(),
                            ])->columnSpan(1),
                    ])->columnSpanFull(),
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
            'index' => Pages\ListJobApplications::route('/'),
            'create' => Pages\CreateJobApplication::route('/create'),
            'view' => Pages\ViewJobApplication::route('/{record}'),
            'edit' => Pages\EditJobApplication::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
