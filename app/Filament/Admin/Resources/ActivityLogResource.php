<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ActivityLogResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Log Aktivitas';

    protected static ?string $modelLabel = 'Log Aktivitas';

    protected static ?string $pluralModelLabel = 'Log Aktivitas';

    protected static ?string $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 2;

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Informasi Dasar')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        \Filament\Infolists\Components\Grid::make(3)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('log_name')
                                    ->label('Nama Log')
                                    ->badge(),
                                \Filament\Infolists\Components\TextEntry::make('description')
                                    ->label('Deskripsi'),
                                \Filament\Infolists\Components\TextEntry::make('created_at')
                                    ->label('Waktu Aktivitas')
                                    ->dateTime('d/m/Y H:i:s'),
                            ]),
                    ]),

                \Filament\Infolists\Components\Grid::make(3)
                    ->schema([
                        \Filament\Infolists\Components\Section::make('Subjek')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('subject_type')
                                    ->label('Tipe Subjek'),
                                \Filament\Infolists\Components\TextEntry::make('subject_id')
                                    ->label('ID Subjek'),
                            ])->columnSpan(1),

                        \Filament\Infolists\Components\Section::make('Pelaku (Pelaksana)')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('causer_id')
                                    ->label('ID Pelaku')
                                    ->placeholder('System / Otomatis'),
                                \Filament\Infolists\Components\TextEntry::make('causer_type')
                                    ->label('Tipe Pelaku')
                                    ->placeholder('-'),
                            ])->columnSpan(1),

                        \Filament\Infolists\Components\Section::make('Konteks')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('event')
                                    ->label('Event')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'created' => 'success',
                                        'updated' => 'warning',
                                        'deleted' => 'danger',
                                        default => 'gray',
                                    }),
                                \Filament\Infolists\Components\TextEntry::make('batch_uuid')
                                    ->label('Batch ID')
                                    ->placeholder('-'),
                            ])->columnSpan(1),
                    ]),

                \Filament\Infolists\Components\Section::make('Detail Perubahan')
                    ->icon('heroicon-m-arrow-path')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('details')
                            ->label('Detail Perubahan Data')
                            ->html()
                            ->state(function ($record) {
                                $props = $record->properties;
                                if (empty($props)) return 'Tidak ada detail perubahan.';
                                
                                $old = $props['old'] ?? [];
                                $new = $props['attributes'] ?? [];

                                if (empty($old) && !empty($new)) {
                                    $html = '<table class="w-full text-sm text-left border-collapse border border-gray-200 dark:border-gray-700">';
                                    $html .= '<thead class="bg-gray-50 dark:bg-gray-800"><tr>';
                                    $html .= '<th class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">Kolom</th>';
                                    $html .= '<th class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">Nilai Baru</th>';
                                    $html .= '</tr></thead><tbody>';
                                    
                                    foreach ($new as $key => $value) {
                                        $valStr = is_array($value) ? json_encode($value) : (string)$value;
                                        $html .= '<tr>';
                                        $html .= '<td class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 font-medium">' . ucwords(str_replace('_', ' ', $key)) . '</td>';
                                        $html .= '<td class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">' . ($valStr ?: '-') . '</td>';
                                        $html .= '</tr>';
                                    }
                                    $html .= '</tbody></table>';
                                    return $html;
                                }

                                if (!empty($old) && !empty($new)) {
                                    $html = '<table class="w-full text-sm text-left border-collapse border border-gray-200 dark:border-gray-700">';
                                    $html .= '<thead class="bg-gray-50 dark:bg-gray-800"><tr>';
                                    $html .= '<th class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 font-bold">Kolom</th>';
                                    $html .= '<th class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 font-bold text-danger-600">Sebelum</th>';
                                    $html .= '<th class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 font-bold text-success-600">Sesudah</th>';
                                    $html .= '</tr></thead><tbody>';
                                    
                                    $changed = false;
                                    foreach ($new as $key => $newValue) {
                                        $oldValue = $old[$key] ?? null;
                                        
                                        $oldStr = is_array($oldValue) ? json_encode($oldValue) : (string)$oldValue;
                                        $newStr = is_array($newValue) ? json_encode($newValue) : (string)$newValue;

                                        if ($oldStr !== $newStr) {
                                            $changed = true;
                                            $html .= '<tr>';
                                            $html .= '<td class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 font-medium">' . ucwords(str_replace('_', ' ', $key)) . '</td>';
                                            $html .= '<td class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 text-danger-500 line-through">' . ($oldStr ?: '(kosong)') . '</td>';
                                            $html .= '<td class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 text-success-500 font-bold">' . ($newStr ?: '(kosong)') . '</td>';
                                            $html .= '</tr>';
                                        }
                                    }
                                    $html .= '</tbody></table>';
                                    return $changed ? $html : 'Tidak ada perbedaan nilai terdeteksi.';
                                }

                                return '<pre class="p-2 bg-gray-100 dark:bg-gray-800 rounded">' . json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                            })
                            ->fontFamily('mono')
                            ->prose(),
                    ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Nama Log')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Tipe Subjek')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label('ID Subjek')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label('Nama Log'),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->label('Tipe Subjek'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
