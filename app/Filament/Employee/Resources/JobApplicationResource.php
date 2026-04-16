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
use App\Models\MasterEmployeeNonPermanentSalary;
use App\Models\InterviewProcess;
use Illuminate\Support\Str;
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
                                    ->placeholder('Masukkan nama lengkap sesuai KTP')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('id_number')
                                    ->label('NIK (KTP)')
                                    ->required()
                                    ->numeric()
                                    ->minLength(16)
                                    ->maxLength(16)
                                    ->placeholder('Contoh: 3302123456780001')
                                    ->live()
                                    ->helperText(fn ($state) => (strlen($state) ?? 0) . ' / 16 Digit')
                                    ->unique(ignoreRecord: true),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('place_birth')
                                    ->label('Tempat Lahir')
                                    ->required()
                                    ->placeholder('Contoh: Purbalingga')
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
                            ->placeholder('Masukkan alamat lengkap sesuai domisili')
                            ->rows(3),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('phone_number')
                                    ->label('No. Telepon')
                                    ->tel()
                                    ->required()
                                    ->numeric()
                                    ->placeholder('Contoh: 081234567890')
                                    ->maxLength(20)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->placeholder('Contoh: nama@email.com')
                                    ->unique(ignoreRecord: true),
                            ]),
                    ]),

                Forms\Components\Section::make('Dokumen Lamaran')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto Pas')
                            ->image()
                            ->imageEditor()
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth(800)
                            ->imageResizeTargetHeight(800)
                            ->directory('job-applications/photos')
                            ->visibility('public')
                            ->maxSize(15360)
                            ->required()
                            ->optimize('webp')
                            ->helperText('Unggah foto formal terbaru (background merah/biru, berpakaian rapi, format JPG/PNG, maks. 2MB).'),

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
                            ->placeholder('Contoh: Universitas Gadjah Mada')
                            ->maxLength(255),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('education_major')
                                    ->label('Jurusan')
                                    ->placeholder('Contoh: Teknik Informatika')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('education_graduation_year')
                                    ->label('Tahun Lulus')
                                    ->numeric()
                                    ->required()
                                    ->placeholder('Contoh: 2022')
                                    ->minValue(1980)
                                    ->maxValue(date('Y')),
                            ]),

                        Forms\Components\TextInput::make('education_gpa')
                            ->label('IPK/Nilai')
                            ->numeric()
                            ->step(0.01)
                            ->placeholder('Contoh: 3.75')
                            ->minValue(0)
                            ->maxValue(5),
                    ]),

                Forms\Components\Section::make('Pengalaman Kerja Terakhir')
                    ->schema([
                        Forms\Components\TextInput::make('last_company_name')
                            ->label('Nama Perusahaan')
                            ->placeholder('Contoh: PT. Maju Jaya')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('last_position')
                            ->label('Posisi')
                            ->placeholder('Contoh: Staff Administrasi')
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
                            ->prefix('Rp')
                            ->placeholder('Masukkan nominal gaji terakhir'),
                    ]),

                Forms\Components\Section::make('Ekspektasi')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('expected_salary')
                                    ->label('Ekspektasi Gaji')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('Masukkan nominal gaji yang diharapkan'),
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
                                    ->placeholder('Nama orang yang bisa dihubungi untuk referensi')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('reference_phone')
                                    ->label('No. Telepon Referensi')
                                    ->tel()
                                    ->placeholder('No. Telepon pemberi referensi')
                                    ->maxLength(20),
                            ]),

                        Forms\Components\TextInput::make('reference_relation')
                            ->label('Hubungan dengan Referensi')
                            ->placeholder('Contoh: Atasan / Rekan Kerja')
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
                    ->label('Nama Lengkap')
                    ->description(fn (JobApplication $record): string => $record->application_number)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Pribadi')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'danger',
                        default => 'gray',
                    })
                    ->description(fn (JobApplication $record): string => 
                        ($record->date_birth ? $record->date_birth->age . ' Thn' : '-') . ' / ' .
                        ($record->marital_status ? match ($record->marital_status) {
                            'single' => 'Belum Menikah',
                            'married' => 'Menikah',
                            'divorced' => 'Cerai',
                            'widowed' => 'Janda/Duda',
                            default => $record->marital_status,
                        } : '')
                    )
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Kontak')
                    ->description(fn (JobApplication $record): string => $record->phone_number ?? '')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('appliedPosition.name')
                    ->label('Posisi & Bagian')
                    ->description(fn (JobApplication $record): string => $record->appliedDepartment->name ?? '')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('educationLevel.name')
                    ->label('Pendidikan')
                    ->description(fn (JobApplication $record): string => ($record->education_institution ?? '') . ($record->education_major ? " - " . $record->education_major : ""))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_company_name')
                    ->label('Pengalaman Terakhir')
                    ->description(fn (JobApplication $record): string => $record->last_position ?? '')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('expected_salary')
                    ->label('Ekspektasi Gaji')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('available_start_date')
                    ->label('Tgl Mulai Kerja')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('interview_results.last_result')
                    ->label('Interview')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'passed' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'passed' => 'Lulus',
                        'failed' => 'Gagal',
                        'pending' => 'Menunggu',
                        default => '-',
                    })
                    ->description(fn (JobApplication $record): string => 
                        isset($record->interview_results['last_score']) 
                            ? "Skor: {$record->interview_results['last_score']}" 
                            : (isset($record->interview_results['overall_score']) ? "Skor: {$record->interview_results['overall_score']}%" : "")
                    )
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'submitted' => 'secondary',
                        'reviewed' => 'warning',
                        'interview_scheduled' => 'info',
                        'interviewed' => 'primary',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'withdrawn' => 'gray',
                        default => 'gray',
                    })
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
                    ->label('Bagian')
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
                Tables\Actions\Action::make('schedule_interview')
                    ->label('Jadwalkan')
                    ->button('')
                    ->color('primary')
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
                    ->color('success')
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
                    ->label('Proses')
                    ->button('')
                    ->color('primary')
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
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn(Forms\Set $set) => $set('proposed_employment_status_id', null)),

                                Forms\Components\Select::make('proposed_employment_status_id')
                                    ->label('Status Kepegawaian')
                                    ->options(function (Forms\Get $get) {
                                        $agreementId = $get('proposed_agreement_type_id');
                                        if ($agreementId == 1) { // PKWT
                                             return MasterEmployeeStatusEmployment::whereIn('id', [1, 2, 3])->pluck('name', 'id');
                                        } elseif ($agreementId == 2) { // PKWTT
                                             return MasterEmployeeStatusEmployment::whereIn('id', [4, 5])->pluck('name', 'id');
                                        }
                                        return MasterEmployeeStatusEmployment::where('id', '!=', 6)->pluck('name', 'id');
                                    })
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $set('proposed_grade_id', null);
                                        $set('proposed_non_permanent_salary_id', null);
                                        $set('proposed_salary', null);
                                    }),

                                Forms\Components\Select::make('proposed_non_permanent_salary_id')
                                    ->label('Referensi Gaji Pokok')
                                    ->options(fn(Forms\Get $get) => MasterEmployeeNonPermanentSalary::where('employment_status_id', $get('proposed_employment_status_id'))->pluck('name', 'id'))
                                    ->required(fn(Forms\Get $get): bool => in_array($get('proposed_employment_status_id'), [1, 2, 3]))
                                    ->visible(fn(Forms\Get $get): bool => in_array($get('proposed_employment_status_id'), [1, 2, 3]))
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $salary = MasterEmployeeNonPermanentSalary::find($state);
                                            if ($salary) {
                                                $set('proposed_salary', $salary->amount);
                                            }
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
                                            if ($grade) {
                                                $set('proposed_salary', $grade->basic_salary);
                                            }
                                        }
                                    })
                                    ->helperText('Pilih golongan untuk auto-fill gaji'),

                                Forms\Components\TextInput::make('proposed_salary')
                                    ->label('Gaji Pokok')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->step(1)
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

                        if ($data['decision'] === 'accepted') {
                            $archive = JobApplicationArchive::createFromJobApplication($record, $data);
                            $agreement = EmployeeAgreement::createFromJobApplication($archive);

                            $firstName = Str::slug(Str::before($record->name, ' '), '');
                            $officeEmail = $firstName . '@pdampurbalingga.co.id';
                            
                            $counter = 1;
                            while (\App\Models\Employee::where('office_email', $officeEmail)->exists() || \App\Models\User::where('email', $officeEmail)->exists()) {
                                $officeEmail = $firstName . $counter . '@pdampurbalingga.co.id';
                                $counter++;
                            }

                            $user = \App\Models\User::create([
                                'name' => $record->name,
                                'email' => $officeEmail,
                                'password' => \Illuminate\Support\Facades\Hash::make('pdam891706'),
                                'is_verified' => true,
                            ]);
                            $user->assignRole('user');

                            $employee = \App\Models\Employee::updateOrCreate(
                                ['email' => $record->email],
                                [
                                    'name' => $record->name,
                                    'phone_number' => $record->phone_number,
                                    'id_number' => $record->id_number,
                                    'image' => $record->photo,
                                    'office_email' => $officeEmail,
                                    'users_id' => $user->id,
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
                                    'non_permanent_salary_id' => $data['proposed_non_permanent_salary_id'] ?? null,
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
                            JobApplicationArchive::createFromJobApplication($record, $data);
                            Notification::make()
                                ->title('Keputusan berhasil diproses')
                                ->body('Lamaran telah ditolak dan diarsipkan')
                                ->success()
                                ->send();
                        }
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat')
                        ->icon('heroicon-o-eye')
                        ->color('info'),
                    Tables\Actions\Action::make('print')
                        ->label('Cetak Profil')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn(JobApplication $record): string => route('job-applications.print', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->visible(
                            fn(?JobApplication $record): bool =>
                            $record && !in_array($record->status, ['accepted', 'rejected'])
                        ),
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

                            JobApplicationArchive::createFromJobApplication($record, [
                                'decision' => 'archived',
                                'decision_reason' => $data['decision_reason'],
                                'decision_date' => now()->toDateString(),
                            ]);

                            $record->update(['status' => 'archived']);
                            $record->delete();

                            Notification::make()
                                ->title('Lamaran berhasil diarsipkan')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make()->label('Hapus'),
                    Tables\Actions\ForceDeleteAction::make()->label('Hapus Permanen'),
                    Tables\Actions\RestoreAction::make()->label('Pulihkan'),
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
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ])->label('Hapus yang Dipilih'),
            ])
            ->defaultSort('submitted_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make(3)
                    ->schema([
                        // Sidebar: Profil & Status
                        Infolists\Components\Group::make([
                            Infolists\Components\Section::make('Profil Pelamar')
                                ->headerActions([
                                    Infolists\Components\Actions\Action::make('print')
                                        ->label('Cetak Profil')
                                        ->icon('heroicon-o-printer')
                                        ->color('info')
                                        ->url(fn(JobApplication $record): string => route('job-applications.print', $record))
                                        ->openUrlInNewTab(),
                                ])
                                ->schema([
                                    Infolists\Components\ImageEntry::make('photo')
                                        ->label('')
                                        ->circular()
                                        ->height(200)
                                        ->alignCenter()
                                        ->extraImgAttributes(['class' => 'shadow-lg border-2 border-primary-500'])
                                        ->placeholder('Tidak ada foto'),
                                    
                                    Infolists\Components\TextEntry::make('name')
                                        ->label('')
                                        ->weight('bold')
                                        ->size('lg')
                                        ->alignCenter(),
                                    
                                    Infolists\Components\TextEntry::make('application_number')
                                        ->label('')
                                        ->color('gray')
                                        ->size('sm')
                                        ->alignCenter()
                                        ->copyable(),

                                    Infolists\Components\TextEntry::make('status')
                                        ->label('Status Saat Ini')
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
                                        })
                                        ->alignCenter()
                                        ->extraAttributes(['class' => 'mt-4']),

                                    Infolists\Components\RepeatableEntry::make('documents')
                                        ->label('Dokumen Lampiran')
                                        ->schema([
                                            Infolists\Components\TextEntry::make('')
                                                ->formatStateUsing(fn ($state) => basename($state))
                                                ->weight('medium')
                                                ->suffixAction(
                                                    Infolists\Components\Actions\Action::make('download')
                                                        ->label('Unduh')
                                                        ->icon('heroicon-o-arrow-down-tray')
                                                        ->color('primary')
                                                        ->url(fn ($state) => asset('storage/' . $state))
                                                        ->openUrlInNewTab()
                                                ),
                                        ])
                                        ->grid(1)
                                        ->placeholder('Tidak ada dokumen'),
                                ]),
                        ])->columnSpan(1),

                        // Main Content: Tabs
                        Infolists\Components\Group::make([
                            Infolists\Components\Tabs::make('Detail Lamaran')
                                ->tabs([
                                    Infolists\Components\Tabs\Tab::make('Data Pribadi')
                                        ->icon('heroicon-o-identification')
                                        ->schema([
                                            Infolists\Components\Grid::make(2)
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('id_number')
                                                        ->label('NIK (KTP)')
                                                        ->icon('heroicon-o-finger-print'),
                                                    Infolists\Components\TextEntry::make('email')
                                                        ->label('Email')
                                                        ->icon('heroicon-o-envelope'),
                                                    Infolists\Components\TextEntry::make('phone_number')
                                                        ->label('Nomor Telepon')
                                                        ->icon('heroicon-o-phone'),
                                                    Infolists\Components\TextEntry::make('gender_label')
                                                        ->label('Jenis Kelamin')
                                                        ->icon('heroicon-o-users'),
                                                    Infolists\Components\TextEntry::make('marital_status_label')
                                                        ->label('Status Sipil'),
                                                    Infolists\Components\TextEntry::make('birth_info')
                                                        ->label('Tempat, Tgl Lahir')
                                                        ->state(fn($record) => "{$record->place_birth}, " . $record->date_birth->format('d F Y')),
                                                ]),
                                            Infolists\Components\TextEntry::make('address')
                                                ->label('Alamat Lengkap')
                                                ->prose(),
                                        ]),

                                    Infolists\Components\Tabs\Tab::make('Info Lamaran')
                                        ->icon('heroicon-o-information-circle')
                                        ->schema([
                                            Infolists\Components\Grid::make(2)
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('submitted_at')
                                                        ->label('Tanggal Melamar')
                                                        ->date('d F Y')
                                                        ->icon('heroicon-o-calendar'),
                                                    Infolists\Components\TextEntry::make('expected_salary')
                                                        ->label('Ekspektasi Gaji')
                                                        ->money('IDR')
                                                        ->icon('heroicon-o-banknotes'),
                                                    Infolists\Components\TextEntry::make('available_start_date')
                                                        ->label('Tersedia Mulai')
                                                        ->date('d F Y')
                                                        ->icon('heroicon-o-clock'),
                                                ]),
                                        ]),

                                    Infolists\Components\Tabs\Tab::make('Referensi')
                                        ->icon('heroicon-o-user-group')
                                        ->schema([
                                            Infolists\Components\Grid::make(2)
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('reference_name')
                                                        ->label('Nama')
                                                        ->icon('heroicon-o-user'),
                                                    Infolists\Components\TextEntry::make('reference_phone')
                                                        ->label('Telepon')
                                                        ->icon('heroicon-o-phone'),
                                                ]),
                                        ]),

                                    Infolists\Components\Tabs\Tab::make('Pendidikan & Karir')
                                        ->icon('heroicon-o-academic-cap')
                                        ->schema([
                                            Infolists\Components\Section::make('Pendidikan Terakhir')
                                                ->schema([
                                                    Infolists\Components\Grid::make(2)
                                                        ->schema([
                                                            Infolists\Components\TextEntry::make('education_summary')
                                                                ->label('Institusi')
                                                                ->state(fn($record) => "{$record->educationLevel->name} - {$record->education_institution}"),
                                                            Infolists\Components\TextEntry::make('education_major')
                                                                ->label('Jurusan'),
                                                            Infolists\Components\TextEntry::make('education_graduation_year')
                                                                ->label('Tahun Lulus'),
                                                            Infolists\Components\TextEntry::make('education_gpa')
                                                                ->label('IPK / Nilai'),
                                                        ]),
                                                ])->compact(),

                                            Infolists\Components\Section::make('Pengalaman Terakhir')
                                                ->schema([
                                                    Infolists\Components\Grid::make(3)
                                                        ->schema([
                                                            Infolists\Components\TextEntry::make('last_company_name')->label('Perusahaan'),
                                                            Infolists\Components\TextEntry::make('last_position')->label('Jabatan'),
                                                            Infolists\Components\TextEntry::make('last_salary')->label('Gaji Terakhir')->money('IDR'),
                                                        ]),
                                                    Infolists\Components\TextEntry::make('last_work_description')
                                                        ->label('Deskripsi Pekerjaan')
                                                        ->prose(),
                                                ])->compact(),
                                        ]),

                                    Infolists\Components\Tabs\Tab::make('Riwayat Interview')
                                        ->icon('heroicon-o-chat-bubble-left-right')
                                        ->schema([
                                            Infolists\Components\RepeatableEntry::make('interviewProcesses')
                                                ->hiddenLabel()
                                                ->schema([
                                                    Infolists\Components\Grid::make(4)
                                                        ->schema([
                                                            Infolists\Components\TextEntry::make('interview_type')
                                                                ->label('Tahap / Jenis')
                                                                ->state(fn($record) => "Tahap-{$record->interview_stage}: {$record->interview_type}")
                                                                ->weight('bold'),
                                                            Infolists\Components\TextEntry::make('interview_date')
                                                                ->label('Waktu')
                                                                ->formatStateUsing(fn($record) => $record->interview_date->format('d M Y') . ($record->interview_time ? ' (' . $record->interview_time . ')' : '')),
                                                            Infolists\Components\TextEntry::make('result')
                                                                ->label('Hasil')
                                                                ->badge()
                                                                ->color(fn(string $state): string => match ($state) {
                                                                    'passed' => 'success',
                                                                    'failed' => 'danger',
                                                                    'pending' => 'warning',
                                                                    default => 'gray',
                                                                })
                                                                ->formatStateUsing(fn(string $state): string => match ($state) {
                                                                    'passed' => 'Lulus',
                                                                    'failed' => 'Gagal',
                                                                    'pending' => 'Menunggu',
                                                                    default => $state,
                                                                }),
                                                            Infolists\Components\TextEntry::make('score')
                                                                ->label('Skor')
                                                                ->suffix('%')
                                                                ->weight('bold'),
                                                        ]),
                                                    Infolists\Components\Grid::make(2)
                                                        ->schema([
                                                            Infolists\Components\TextEntry::make('interviewer_name')
                                                                ->label('Pewawancara')
                                                                ->icon('heroicon-o-user-circle'),
                                                            Infolists\Components\TextEntry::make('feedback')
                                                                ->label('Feedback')
                                                                ->prose(),
                                                        ]),
                                                ])
                                                ->placeholder('Belum ada tahapan interview yang dilaksanakan')
                                                ->grid(1),
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
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }
}
