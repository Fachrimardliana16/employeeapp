<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\ActivityLogResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Log Aktivitas Saya';

    protected static ?string $modelLabel = 'Log Aktivitas';

    protected static ?string $pluralModelLabel = 'Log Aktivitas';

    protected static ?string $navigationGroup = 'Bantuan';

    protected static ?int $navigationSort = 1000;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->hasRole("superadmin")) {
            return $query;
        }

        return $query->where("causer_id", auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Aktivitas')
                    ->schema([
                        Forms\Components\TextInput::make('log_name')
                            ->label('Kategori'),
                        Forms\Components\TextInput::make('description')
                            ->label('Deskripsi'),
                        Forms\Components\TextInput::make('subject_type')
                            ->label('Tipe Subjek'),
                        Forms\Components\TextInput::make('subject_id')
                            ->label('ID Subjek'),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Waktu Aktivitas'),
                        Forms\Components\KeyValue::make('properties')
                            ->label('Data Perubahan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Kategori')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Tipe Subjek')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal & Waktu')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label('Kategori')
                    ->options(fn () => Activity::where('causer_id', auth()->id())->distinct()->pluck('log_name', 'log_name')->toArray()),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('cetak_log')
                    ->label('Cetak Log')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal')
                            ->default(now()->startOfMonth()),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Sampai Tanggal')
                            ->default(now()),
                    ])
                    ->action(function (array $data) {
                        $query = Activity::query()
                            ->where('causer_id', auth()->id());

                        if ($data['date_from']) {
                            $query->whereDate('created_at', '>=', $data['date_from']);
                        }
                        if ($data['date_until']) {
                            $query->whereDate('created_at', '<=', $data['date_until']);
                        }

                        $records = $query->orderBy('created_at', 'desc')->get();

                        $pdf = Pdf::loadView('pdf.activity-log', [
                            'title' => 'Log Aktivitas Saya',
                            'data' => $records,
                            'startDate' => $data['date_from'],
                            'endDate' => $data['date_until'],
                            'userName' => auth()->user()->name,
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'Log_Aktivitas_' . auth()->user()->name . '_' . now()->format('YmdHis') . '.pdf');
                    })
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }
}
