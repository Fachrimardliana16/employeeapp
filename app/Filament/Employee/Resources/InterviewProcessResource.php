<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\InterviewProcessResource\Pages;
use App\Models\InterviewProcess;
use App\Models\JobApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class InterviewProcessResource extends Resource
{
    protected static ?string $model = InterviewProcess::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Rekrutmen & Seleksi';
    protected static ?string $navigationLabel = 'Proses Interview';
    protected static ?int $navigationSort = 102;
    protected static ?string $modelLabel = 'Proses Interview';
    protected static ?string $pluralModelLabel = 'Proses Interview';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\Select::make('job_application_id')
                            ->label('Pelamar')
                            ->relationship('jobApplication', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('interview_stage')
                                    ->label('Tahap ke-')
                                    ->required()
                                    ->numeric()
                                    ->default(1),
                                Forms\Components\Select::make('interview_type')
                                    ->label('Jenis Interview')
                                    ->options(InterviewProcess::getInterviewTypeOptions())
                                    ->required(),
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options(InterviewProcess::getStatusOptions())
                                    ->required()
                                    ->default('scheduled'),
                            ]),
                    ]),

                Forms\Components\Section::make('Jadwal & Lokasi')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('interview_date')
                                    ->label('Tanggal')
                                    ->required(),
                                Forms\Components\TextInput::make('interview_time')
                                    ->label('Jam')
                                    ->placeholder('contoh: 09:00')
                                    ->required(),
                                Forms\Components\TextInput::make('interview_location')
                                    ->label('Lokasi')
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('interviewer_name')
                                    ->label('Nama Pewawancara')
                                    ->maxLength(255),
                                Forms\Components\Select::make('interviewer_id')
                                    ->label('Pewawancara (User System)')
                                    ->relationship('interviewer', 'name')
                                    ->searchable(),
                            ]),
                    ]),

                Forms\Components\Section::make('Hasil & Feedback')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('result')
                                    ->label('Hasil')
                                    ->options(InterviewProcess::getResultOptions())
                                    ->required()
                                    ->default('pending'),
                                Forms\Components\TextInput::make('score')
                                    ->label('Nilai')
                                    ->numeric(),
                            ]),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Internal')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('feedback')
                            ->label('Feedback untuk Pelamar')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jobApplication.application_number')
                    ->label('No. Lamaran')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jobApplication.name')
                    ->label('Nama Pelamar')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('interview_type')
                    ->label('Jenis')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('interview_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('interviewer_name')
                    ->label('Nama Pewawancara')
                    ->searchable(),
                Tables\Columns\TextColumn::make('interviewer.name')
                    ->label('Pewawancara (User System)')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('result')
                    ->label('Hasil')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'passed',
                        'danger' => 'failed',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'passed' => 'Lulus',
                        'failed' => 'Gagal',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'info' => 'scheduled',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'scheduled' => 'Terjadwal',
                        'completed' => 'Selesai',
                        'cancelled' => 'Batal',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('result')
                    ->label('Hasil')
                    ->options(InterviewProcess::getResultOptions()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(InterviewProcess::getStatusOptions()),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('update_result')
                        ->label('Update Hasil')
                        ->icon('heroicon-o-pencil-square')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('result')
                                ->label('Hasil Akhir')
                                ->options(InterviewProcess::getResultOptions())
                                ->required(),
                            Forms\Components\TextInput::make('score')
                                ->label('Nilai')
                                ->numeric(),
                            Forms\Components\Textarea::make('feedback')
                                ->label('Feedback / Catatan')
                                ->rows(3),
                        ])
                        ->action(function (InterviewProcess $record, array $data): void {
                            $record->update([
                                'result' => $data['result'],
                                'score' => $data['score'],
                                'feedback' => $data['feedback'],
                                'status' => 'completed',
                            ]);

                            // Sync back to JobApplication
                            $jobApplication = $record->jobApplication;
                            if ($jobApplication) {
                                // Update status to interviewed
                                $jobApplication->update([
                                    'status' => 'interviewed',
                                    'interview_results' => array_merge($jobApplication->interview_results ?? [], [
                                        'last_stage' => $record->interview_stage,
                                        'last_type' => $record->interview_type,
                                        'last_result' => $data['result'],
                                        'last_score' => $data['score'],
                                        'last_feedback' => $data['feedback'],
                                        'updated_at' => now(),
                                    ])
                                ]);
                            }

                            Notification::make()
                                ->title('Hasil interview diperbarui')
                                ->body('Status lamaran otomatis berubah menjadi "Sudah Interview"')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\ViewAction::make()->label('Lihat'),
                    Tables\Actions\EditAction::make()->label('Edit'),
                    Tables\Actions\DeleteAction::make()->label('Hapus'),
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
                ])->label('Hapus yang Dipilih'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInterviewProcesses::route('/'),
            'create' => Pages\CreateInterviewProcess::route('/create'),
            'edit' => Pages\EditInterviewProcess::route('/{record}/edit'),
        ];
    }
}

