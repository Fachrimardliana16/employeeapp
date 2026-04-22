<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\JobApplicationArchiveResource\Pages;
use App\Models\JobApplicationArchive;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobApplicationArchiveResource extends Resource
{
    protected static ?string $model = JobApplicationArchive::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Arsip Lamaran';

    protected static ?string $modelLabel = 'Arsip Lamaran';

    protected static ?string $pluralModelLabel = 'Arsip Lamaran';

    protected static ?string $navigationGroup = 'Rekrutmen & Seleksi';

    protected static ?int $navigationSort = 103;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Keputusan')
                    ->schema([
                        Forms\Components\Select::make('job_application_id')
                            ->label('Lamaran Kerja')
                            ->relationship('jobApplication', 'name')
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('decision')
                            ->label('Keputusan')
                            ->options([
                                'accepted' => 'Diterima',
                                'rejected' => 'Ditolak',
                            ])
                            ->required()
                            ->disabled(),

                        Forms\Components\Textarea::make('decision_reason')
                            ->label('Alasan Keputusan')
                            ->required()
                            ->disabled()
                            ->rows(3),

                        Forms\Components\DatePicker::make('decision_date')
                            ->label('Tanggal Keputusan')
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('decided_by')
                            ->label('Diputuskan Oleh')
                            ->relationship('decidedByUser', 'name')
                            ->disabled(),
                    ]),

                Forms\Components\Section::make('Data Kontrak (Jika Diterima)')
                    ->schema([
                        Forms\Components\Select::make('proposed_agreement_type_id')
                            ->label('Jenis Kontrak')
                            ->relationship('proposedAgreementType', 'name')
                            ->disabled(),

                        Forms\Components\Select::make('proposed_employment_status_id')
                            ->label('Status Kepegawaian')
                            ->relationship('proposedEmploymentStatus', 'name')
                            ->disabled(),

                        Forms\Components\Select::make('proposed_grade_id')
                            ->label('Golongan')
                            ->relationship('proposedGrade', 'name')
                            ->disabled(),

                        Forms\Components\TextInput::make('proposed_salary')
                            ->label('Gaji Pokok')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),

                        Forms\Components\DatePicker::make('proposed_start_date')
                            ->label('Tanggal Mulai Kerja')
                            ->disabled(),
                    ])
                    ->visible(fn($record) => $record?->decision === 'accepted'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('snapshot_name')
                    ->label('Pelamar')
                    ->description(fn (JobApplicationArchive $record): string => $record->snapshot_app_number)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('snapshot_position')
                    ->label('Posisi')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('decision')
                    ->label('Keputusan')
                    ->colors([
                        'success' => 'accepted',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'accepted' => 'Diterima',
                        'rejected' => 'Ditolak',
                        default => $state,
                    })
                    ->description(fn (JobApplicationArchive $record): string => $record->decision_date->format('d/m/Y')),

                Tables\Columns\TextColumn::make('decidedByUser.name')
                    ->label('Diputuskan Oleh')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('has_employee_agreement')
                    ->label('Kontrak')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->employeeAgreement()->exists()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('decision')
                    ->label('Keputusan')
                    ->options([
                        'accepted' => 'Diterima',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\Filter::make('decision_date')
                    ->form([
                        Forms\Components\DatePicker::make('decision_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('decision_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['decision_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('decision_date', '>=', $date),
                            )
                            ->when(
                                $data['decision_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('decision_date', '<=', $date),
                            );
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),

                    Tables\Actions\Action::make('view_employee_agreement')
                        ->label('Lihat Kontrak')
                        ->icon('heroicon-o-document')
                        ->color('info')
                        ->visible(fn($record) => $record->decision === 'accepted' && $record->employeeAgreement()->exists())
                        ->url(fn($record) => route('filament.employee.resources.employee-agreements.view', [
                            'record' => $record->employeeAgreement->id
                        ])),
                    Tables\Actions\DeleteAction::make()->label('Hapus'),
                    Tables\Actions\RestoreAction::make()->label('Pulihkan'),
                    Tables\Actions\ForceDeleteAction::make()->label('Hapus Permanen'),
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
            ->defaultSort('decision_date', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make(3)
                    ->schema([
                        // Sidebar: Profil Singkat
                        Infolists\Components\Group::make([
                            Infolists\Components\Section::make('Profil Pelamar')
                                ->schema([
                                    Infolists\Components\ImageEntry::make('snapshot_photo')
                                        ->label('')
                                        ->circular()
                                        ->height(200)
                                        ->alignCenter()
                                        ->disk('public')
                                        ->imageUrl(fn($state) => $state ? url('image-view/' . $state) : null)
                                        ->extraAttributes(['class' => 'p-4 bg-gray-50 rounded-xl mb-4'])
                                        ->placeholder('Tidak ada foto'),
                                    
                                    Infolists\Components\TextEntry::make('snapshot_name')
                                        ->label('')
                                        ->weight('bold')
                                        ->size('lg')
                                        ->alignCenter(),
                                    
                                    Infolists\Components\TextEntry::make('snapshot_app_number')
                                        ->label('')
                                        ->color('gray')
                                        ->size('sm')
                                        ->alignCenter(),

                                    Infolists\Components\TextEntry::make('decision')
                                        ->label('Keputusan Akhir')
                                        ->badge()
                                        ->color(fn(string $state): string => match ($state) {
                                            'accepted' => 'success',
                                            'rejected' => 'danger',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn(string $state): string => match ($state) {
                                            'accepted' => 'DITERIMA',
                                            'rejected' => 'DITOLAK',
                                            default => strtoupper($state),
                                        })
                                        ->alignCenter()
                                        ->extraAttributes(['class' => 'mt-4']),
                                ]),

                            Infolists\Components\Section::make('Status Keputusan')
                                ->schema([
                                    Infolists\Components\TextEntry::make('decision_date')
                                        ->label('Tanggal')
                                        ->date('d F Y')
                                        ->icon('heroicon-o-calendar'),
                                    Infolists\Components\TextEntry::make('decidedByUser.name')
                                        ->label('Oleh')
                                        ->icon('heroicon-o-user-circle'),
                                ]),
                        ])->columnSpan(1),

                        // Main Content: Tabs
                        Infolists\Components\Group::make([
                            Infolists\Components\Tabs::make('Detail Lamaran')
                                ->tabs([
                                    Infolists\Components\Tabs\Tab::make('Data Pelamar')
                                        ->icon('heroicon-o-identification')
                                        ->schema([
                                            Infolists\Components\Grid::make(2)
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('snapshot_email')
                                                        ->label('Email')
                                                        ->copyable()
                                                        ->icon('heroicon-o-envelope'),
                                                    Infolists\Components\TextEntry::make('snapshot_position')
                                                        ->label('Posisi Dilamar')
                                                        ->weight('bold')
                                                        ->icon('heroicon-o-briefcase'),
                                                    Infolists\Components\TextEntry::make('snapshot_department')
                                                        ->label('Unit / Bagian')
                                                        ->icon('heroicon-o-building-office'),
                                                    Infolists\Components\TextEntry::make('snapshot_expected_salary')
                                                        ->label('Ekspektasi Gaji')
                                                        ->money('IDR')
                                                        ->icon('heroicon-o-banknotes'),
                                                ]),
                                        ]),

                                    Infolists\Components\Tabs\Tab::make('Keputusan & Kontrak')
                                        ->icon('heroicon-o-document-check')
                                        ->schema([
                                            Infolists\Components\TextEntry::make('decision_reason')
                                                ->label('Alasan / Catatan Keputusan')
                                                ->prose()
                                                ->placeholder('Tidak ada catatan khusus'),

                                            Infolists\Components\Grid::make(2)
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('proposedAgreementType.name')
                                                        ->label('Jenis Kontrak Usulan')
                                                        ->placeholder('N/A'),
                                                    Infolists\Components\TextEntry::make('proposed_salary')
                                                        ->label('Besaran Gaji Usulan')
                                                        ->money('IDR')
                                                        ->placeholder('N/A'),
                                                    Infolists\Components\TextEntry::make('proposed_start_date')
                                                        ->label('Rencana Mulai Kerja')
                                                        ->date('d F Y')
                                                        ->placeholder('N/A'),
                                                ])
                                                ->visible(fn($record) => $record->decision === 'accepted'),
                                            
                                            Infolists\Components\Section::make('Internal HR Tracking')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('employeeAgreement.agreement_number')
                                                        ->label('No. Kontrak Terbit')
                                                        ->weight('bold')
                                                        ->placeholder('Belum ada kontrak yang diterbitkan'),
                                                ])
                                                ->visible(fn($record) => $record->decision === 'accepted' && $record->employeeAgreement()->exists())
                                                ->compact(),
                                        ]),

                                    Infolists\Components\Tabs\Tab::make('Dokumen Lampiran')
                                        ->icon('heroicon-o-paper-clip')
                                        ->schema([
                                            Infolists\Components\RepeatableEntry::make('snapshot_documents')
                                                ->label('')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('')
                                                        ->formatStateUsing(fn($state) => 'Berkas: ' . basename($state))
                                                        ->weight('medium')
                                                        ->suffixAction(
                                                            Infolists\Components\Actions\Action::make('download')
                                                                ->label('Unduh Berkas')
                                                                ->icon('heroicon-o-arrow-down-tray')
                                                                ->color('primary')
                                                                ->url(fn($state) => \Illuminate\Support\Facades\Storage::url($state))
                                                                ->openUrlInNewTab()
                                                        ),
                                                ])
                                                ->grid(2)
                                                ->placeholder('Tidak ada dokumen yang dilampirkan pelamar'),
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
            'index' => Pages\ListJobApplicationArchives::route('/'),
            'view' => Pages\ViewJobApplicationArchive::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canCreate(): bool
    {
        return false; // Archive dibuat otomatis dari proses keputusan
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Archive tidak bisa diedit
    }
}
