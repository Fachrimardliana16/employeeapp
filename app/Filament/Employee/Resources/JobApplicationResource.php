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
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

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
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                            ]),

                        Forms\Components\TextInput::make('id_number')
                            ->label('No. KTP')
                            ->maxLength(16),
                    ]),

                Forms\Components\Section::make('Posisi yang Dilamar')
                    ->schema([
                        Forms\Components\Select::make('applied_department_id')
                            ->label('Departemen')
                            ->options(MasterDepartment::pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn($state, Forms\Set $set) => $set('applied_sub_department_id', null)),

                        Forms\Components\Select::make('applied_sub_department_id')
                            ->label('Sub Departemen')
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
                                    ->minValue(1980)
                                    ->maxValue(date('Y')),
                            ]),

                        Forms\Components\TextInput::make('education_gpa')
                            ->label('IPK/Nilai')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(4),
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
                Tables\Columns\TextColumn::make('application_number')
                    ->label('No. Lamaran')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('appliedPosition.name')
                    ->label('Posisi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('appliedDepartment.name')
                    ->label('Departemen')
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
                    ->label('Tanggal Melamar')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expected_salary')
                    ->label('Ekspektasi Gaji')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('interview_info')
                    ->label('Info Interview')
                    ->getStateUsing(function (?JobApplication $record): ?string {
                        if (!$record || $record->status !== 'interview_scheduled' || !$record->interview_schedule) {
                            return null;
                        }

                        $schedule = $record->interview_schedule;
                        $datetime = isset($schedule['datetime']) ?
                            Carbon::parse($schedule['datetime'])->format('d/m/Y H:i') : '';
                        $location = $schedule['location'] ?? '';
                        return $datetime . ($location ? " di {$location}" : '');
                    })
                    ->placeholder('Belum dijadwalkan')
                    ->wrap(),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(
                        fn(?JobApplication $record): bool =>
                        $record && !in_array($record->status, ['accepted', 'rejected'])
                    ),

                Tables\Actions\Action::make('schedule_interview')
                    ->label('Jadwalkan Interview')
                    ->icon('heroicon-o-calendar')
                    ->color('info')
                    ->visible(
                        fn(?JobApplication $record): bool =>
                        $record && in_array($record->status, ['submitted', 'reviewed'])
                    )
                    ->form([
                        Forms\Components\DateTimePicker::make('interview_datetime')
                            ->label('Tanggal & Waktu Interview')
                            ->required(),
                        Forms\Components\TextInput::make('interview_location')
                            ->label('Lokasi Interview')
                            ->required(),
                        Forms\Components\Textarea::make('interview_notes')
                            ->label('Catatan Interview')
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
                                'scheduled_by' => auth()->id() ?? 0,
                                'scheduled_at' => now(),
                            ],
                        ]);

                        Notification::make()
                            ->title('Interview berhasil dijadwalkan')
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
                                    ->options(MasterEmployeeStatusEmployment::pluck('name', 'id'))
                                    ->required(),

                                Forms\Components\Select::make('proposed_grade_id')
                                    ->label('Grade Gaji')
                                    ->options(MasterEmployeeGrade::all()->pluck('name', 'id'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $grade = MasterEmployeeGrade::find($state);
                                            if ($grade && $grade->basic_salary > 0) {
                                                $set('proposed_salary', $grade->basic_salary);
                                            }
                                        }
                                    })
                                    ->helperText('Pilih grade untuk auto-fill gaji'),

                                Forms\Components\TextInput::make('proposed_salary')
                                    ->label('Gaji Pokok')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->step(100000)
                                    ->helperText('Gaji akan terisi otomatis saat memilih grade')
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

                        // Jika diterima, buat kontrak otomatis
                        if ($data['decision'] === 'accepted') {
                            $agreement = EmployeeAgreement::createFromJobApplication($archive);

                            Notification::make()
                                ->title('Pelamar diterima dan kontrak berhasil dibuat')
                                ->body("Nomor kontrak: {$agreement->agreement_number}")
                                ->success()
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view_contract')
                                        ->label('Lihat Kontrak')
                                        ->url(route('filament.employee.resources.employee-agreements.view', ['record' => $agreement->id]))
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
                Infolists\Components\Section::make('Informasi Lamaran')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('application_number')
                                    ->label('No. Lamaran'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
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
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('submitted_at')
                                    ->label('Tanggal Melamar')
                                    ->dateTime('d/m/Y H:i'),
                                Infolists\Components\TextEntry::make('decision_at')
                                    ->label('Tanggal Keputusan')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('Belum ada keputusan'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Data Pribadi')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Nama Lengkap'),
                                Infolists\Components\TextEntry::make('email')
                                    ->label('Email'),
                            ]),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('place_birth')
                                    ->label('Tempat Lahir'),
                                Infolists\Components\TextEntry::make('date_birth')
                                    ->label('Tanggal Lahir')
                                    ->date('d/m/Y'),
                                Infolists\Components\TextEntry::make('gender_label')
                                    ->label('Jenis Kelamin'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('marital_status_label')
                                    ->label('Status Pernikahan'),
                                Infolists\Components\TextEntry::make('phone_number')
                                    ->label('No. Telepon'),
                            ]),

                        Infolists\Components\TextEntry::make('address')
                            ->label('Alamat'),
                    ]),

                Infolists\Components\Section::make('Posisi yang Dilamar')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('appliedDepartment.name')
                                    ->label('Departemen'),
                                Infolists\Components\TextEntry::make('appliedSubDepartment.name')
                                    ->label('Sub Departemen')
                                    ->placeholder('Tidak ada'),
                            ]),

                        Infolists\Components\TextEntry::make('appliedPosition.name')
                            ->label('Posisi'),
                    ]),

                Infolists\Components\Section::make('Pendidikan')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('educationLevel.name')
                                    ->label('Tingkat Pendidikan'),
                                Infolists\Components\TextEntry::make('education_institution')
                                    ->label('Institusi'),
                            ]),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('education_major')
                                    ->label('Jurusan')
                                    ->placeholder('Tidak disebutkan'),
                                Infolists\Components\TextEntry::make('education_graduation_year')
                                    ->label('Tahun Lulus'),
                                Infolists\Components\TextEntry::make('education_gpa')
                                    ->label('IPK/Nilai')
                                    ->placeholder('Tidak disebutkan'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Pengalaman Kerja Terakhir')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('last_company_name')
                                    ->label('Perusahaan')
                                    ->placeholder('Tidak ada'),
                                Infolists\Components\TextEntry::make('last_position')
                                    ->label('Posisi')
                                    ->placeholder('Tidak ada'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('last_work_start_date')
                                    ->label('Tanggal Mulai')
                                    ->date('d/m/Y')
                                    ->placeholder('Tidak ada'),
                                Infolists\Components\TextEntry::make('last_work_end_date')
                                    ->label('Tanggal Berakhir')
                                    ->date('d/m/Y')
                                    ->placeholder('Tidak ada'),
                            ]),

                        Infolists\Components\TextEntry::make('last_work_description')
                            ->label('Deskripsi Pekerjaan')
                            ->placeholder('Tidak ada'),

                        Infolists\Components\TextEntry::make('last_salary')
                            ->label('Gaji Terakhir')
                            ->money('IDR')
                            ->placeholder('Tidak disebutkan'),
                    ]),

                Infolists\Components\Section::make('Ekspektasi & Referensi')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('expected_salary')
                                    ->label('Ekspektasi Gaji')
                                    ->money('IDR')
                                    ->placeholder('Tidak disebutkan'),
                                Infolists\Components\TextEntry::make('available_start_date')
                                    ->label('Bisa Mulai Kerja')
                                    ->date('d/m/Y')
                                    ->placeholder('Tidak disebutkan'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('reference_name')
                                    ->label('Nama Referensi')
                                    ->placeholder('Tidak ada'),
                                Infolists\Components\TextEntry::make('reference_phone')
                                    ->label('No. Telepon Referensi')
                                    ->placeholder('Tidak ada'),
                            ]),

                        Infolists\Components\TextEntry::make('reference_relation')
                            ->label('Hubungan dengan Referensi')
                            ->placeholder('Tidak disebutkan'),
                    ]),

                Infolists\Components\Section::make('Catatan')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Catatan HR')
                            ->placeholder('Tidak ada catatan'),
                    ])
                    ->visible(fn(JobApplication $record): bool => !empty($record->notes)),
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
