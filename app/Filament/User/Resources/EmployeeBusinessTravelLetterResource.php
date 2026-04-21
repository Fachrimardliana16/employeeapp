<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\EmployeeBusinessTravelLetterResource\Pages;
use App\Models\EmployeeBusinessTravelLetter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EmployeeBusinessTravelLetterResource extends Resource
{
    protected static ?string $model = EmployeeBusinessTravelLetter::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Surat Resmi';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Perjalanan Dinas';
    protected static ?string $modelLabel = 'Surat Perjalanan Dinas';
    protected static ?string $pluralModelLabel = 'Surat Perjalanan Dinas';

    public static function getNavigationBadge(): ?string
    {
        $employee = Auth::user()->employee;
        if (!$employee) return null;
        return static::getModel()::where('status', 'on progress')
            ->where(function ($query) use ($employee) {
                $query->where('employee_id', $employee->id)
                      ->orWhereJsonContains('additional_employee_ids', $employee->id);
            })
            ->whereNotNull('signed_file_path')
            ->count();
    }

    public static function getEloquentQuery(): Builder
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
        $employeeId = $employee->id;
        
        return parent::getEloquentQuery()
            ->whereNotNull('signed_file_path')
            ->where(function (Builder $query) use ($employeeId) {
                $query->where('employee_id', $employeeId)
                      ->orWhereJsonContains('additional_employee_ids', (string)$employeeId)
                      ->orWhereJsonContains('additional_employee_ids', (int)$employeeId);
            });
    }

    public static function canCreate(): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Perjalanan Dinas')
                    ->schema([
                        Forms\Components\TextInput::make('registration_number')->label('Nomor Surat')->readOnly(),
                        Forms\Components\TextInput::make('destination')->label('Tujuan')->readOnly(),
                        Forms\Components\TextInput::make('purpose_of_trip')->label('Maksud Perjalanan')->columnSpanFull()->readOnly(),
                        Forms\Components\TextInput::make('start_date')->label('Berangkat')->readOnly(),
                        Forms\Components\TextInput::make('end_date')->label('Kembali')->readOnly(),
                        Forms\Components\TextInput::make('total_cost')->label('Total Biaya')->prefix('Rp')->readOnly(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')->label('Nomor Surat')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'selesai' => 'success',
                        'on progress' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => strtoupper($state)),

                Tables\Columns\IconColumn::make('archives')
                    ->label('Arsip (I/K)')
                    ->getStateUsing(fn($record) => (bool)$record->signed_file_path && (bool)$record->visit_file_path)
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-document-text')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn($record) => "Internal: " . ($record->signed_file_path ? '✅' : '❌') . " | Kunjungan: " . ($record->visit_file_path ? '✅' : '❌')),

                Tables\Columns\TextColumn::make('destination')
                    ->label('Tujuan')
                    ->description(fn($record) => str($record->purpose_of_trip)->limit(30))
                    ->tooltip(fn($record) => $record->purpose_of_trip),

                Tables\Columns\TextColumn::make('timespan')
                    ->label('Waktu')
                    ->getStateUsing(fn($record) => $record->start_date->format('d/m/y') . ' - ' . $record->end_date->format('d/m/y'))
                    ->description(fn($record) => $record->start_date->diffInDays($record->end_date) + 1 . ' hari'),

                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Biaya')
                    ->money('IDR'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),
                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn(EmployeeBusinessTravelLetter $record) => $record->pdf_file_path ? asset('storage/' . $record->pdf_file_path) : null)
                        ->openUrlInNewTab()
                        ->visible(fn(EmployeeBusinessTravelLetter $record) => !empty($record->pdf_file_path)),
                    Tables\Actions\Action::make('upload_signed')
                        ->label('Selesaikan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            // Perbaikan: gunakan visit_file_path untuk tahap penyelesaian oleh user
                            Forms\Components\FileUpload::make('visit_file_path')
                                ->label('File Scan Cap Kunjungan (PDF)')
                                ->disk('public')
                                ->visibility('public')
                                ->directory('business_travel_letters_complete')
                                ->acceptedFileTypes(['application/pdf'])
                                ->required(),
                        ])
                        ->action(function (EmployeeBusinessTravelLetter $record, array $data) {
                            $record->update([
                                'visit_file_path' => $data['visit_file_path'],
                                'status' => 'selesai',
                            ]);
                        })
                        ->visible(fn(EmployeeBusinessTravelLetter $record) => $record->status !== 'selesai'),
                    Tables\Actions\Action::make('view_signed')
                        ->label('Lihat Arsip')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->form([
                            Forms\Components\Group::make([
                                Forms\Components\Placeholder::make('internal_file')
                                    ->label('Arsip TTD Internal')
                                    ->content(fn($record) => $record->signed_file_path ? new \Illuminate\Support\HtmlString("<a href='".asset('storage/'.$record->signed_file_path)."' target='_blank' class='text-primary-600 underline'>Buka File Internal</a>") : 'Belum diupload'),
                                Forms\Components\Placeholder::make('visit_file')
                                    ->label('Arsip Cap Kunjungan')
                                    ->content(fn($record) => $record->visit_file_path ? new \Illuminate\Support\HtmlString("<a href='".asset('storage/'.$record->visit_file_path)."' target='_blank' class='text-primary-600 underline'>Buka File Kunjungan</a>") : 'Belum diupload'),
                            ])->columns(2)
                        ]),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeBusinessTravelLetters::route('/'),
        ];
    }
}
