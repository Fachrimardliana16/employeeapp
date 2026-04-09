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
                            ->label('Grade Gaji')
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
                Tables\Columns\TextColumn::make('snapshot_app_number')
                    ->label('No. Lamaran')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('snapshot_name')
                    ->label('Nama Pelamar')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('snapshot_position')
                    ->label('Posisi')
                    ->searchable(),

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
                    }),

                Tables\Columns\TextColumn::make('decision_date')
                    ->label('Tanggal Keputusan')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('decidedByUser.name')
                    ->label('Diputuskan Oleh')
                    ->searchable(),

                Tables\Columns\TextColumn::make('proposed_salary')
                    ->label('Gaji Diusulkan')
                    ->money('IDR')
                    ->placeholder('N/A'),

                Tables\Columns\IconColumn::make('has_employee_agreement')
                    ->label('Kontrak Dibuat')
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
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('view_employee_agreement')
                    ->label('Lihat Kontrak')
                    ->icon('heroicon-o-document')
                    ->color('info')
                    ->visible(fn($record) => $record->decision === 'accepted' && $record->employeeAgreement()->exists())
                    ->url(fn($record) => route('filament.employee.resources.employee-agreements.view', [
                        'record' => $record->employeeAgreement->id
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('decision_date', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Keputusan')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('snapshot_app_number')
                                    ->label('No. Lamaran'),
                                Infolists\Components\TextEntry::make('decision')
                                    ->label('Keputusan')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'accepted' => 'success',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'accepted' => 'Diterima',
                                        'rejected' => 'Ditolak',
                                        default => $state,
                                    }),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('decision_date')
                                    ->label('Tanggal Keputusan')
                                    ->date('d/m/Y'),
                                Infolists\Components\TextEntry::make('decidedByUser.name')
                                    ->label('Diputuskan Oleh'),
                            ]),

                        Infolists\Components\TextEntry::make('decision_reason')
                            ->label('Alasan Keputusan'),
                    ]),

                Infolists\Components\Section::make('Data Pelamar')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('snapshot_name')
                                    ->label('Nama'),
                                Infolists\Components\TextEntry::make('snapshot_email')
                                    ->label('Email'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('snapshot_position')
                                    ->label('Posisi'),
                                Infolists\Components\TextEntry::make('snapshot_department')
                                    ->label('Departemen'),
                            ]),

                        Infolists\Components\TextEntry::make('snapshot_expected_salary')
                            ->label('Ekspektasi Gaji')
                            ->money('IDR'),
                    ]),

                Infolists\Components\Section::make('Data Kontrak yang Diusulkan')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('proposedAgreementType.name')
                                    ->label('Jenis Kontrak')
                                    ->placeholder('Tidak disebutkan'),
                                Infolists\Components\TextEntry::make('proposedEmploymentStatus.name')
                                    ->label('Status Kepegawaian')
                                    ->placeholder('Tidak disebutkan'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('proposedGrade.name')
                                    ->label('Golongan')
                                    ->placeholder('Tidak disebutkan'),
                                Infolists\Components\TextEntry::make('proposed_salary')
                                    ->label('Gaji Pokok')
                                    ->money('IDR')
                                    ->placeholder('Tidak disebutkan'),
                            ]),

                        Infolists\Components\TextEntry::make('proposed_start_date')
                            ->label('Tanggal Mulai Kerja')
                            ->date('d/m/Y')
                            ->placeholder('Tidak disebutkan'),
                    ])
                    ->visible(fn($record) => $record->decision === 'accepted'),

                Infolists\Components\Section::make('Status Kontrak')
                    ->schema([
                        Infolists\Components\TextEntry::make('employeeAgreement.agreement_number')
                            ->label('No. Kontrak')
                            ->placeholder('Kontrak belum dibuat'),

                        Infolists\Components\TextEntry::make('employeeAgreement.agreement_date_start')
                            ->label('Tanggal Mulai Kontrak')
                            ->date('d/m/Y')
                            ->placeholder('Kontrak belum dibuat'),
                    ])
                    ->visible(fn($record) => $record->decision === 'accepted'),
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
