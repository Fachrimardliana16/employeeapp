<?php

namespace App\Filament\Employee\Resources\EmployeePromotionResource\Pages;

use App\Filament\Employee\Resources\EmployeePromotionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewEmployeePromotion extends ViewRecord
{
    protected static string $resource = EmployeePromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Promosi')
                    ->schema([
                        Infolists\Components\TextEntry::make('decision_letter_number')
                            ->label('Nomor Surat Keputusan'),
                        Infolists\Components\TextEntry::make('promotion_date')
                            ->label('Tanggal Promosi')
                            ->date('d F Y'),
                    ])->columns(2),

                Infolists\Components\Section::make('Data Pegawai')
                    ->schema([
                        Infolists\Components\TextEntry::make('employee.name')
                            ->label('Nama Pegawai'),
                        Infolists\Components\TextEntry::make('employee.nippam')
                            ->label('NIPPAM'),
                        Infolists\Components\TextEntry::make('employee.department.name')
                            ->label('Departemen'),
                        Infolists\Components\TextEntry::make('employee.position.name')
                            ->label('Posisi'),
                    ])->columns(2),

                Infolists\Components\Section::make('Perubahan Grade & Gaji')
                    ->schema([
                        Infolists\Components\Group::make([
                            Infolists\Components\TextEntry::make('oldSalaryGrade.name')
                                ->label('Grade Lama')
                                ->badge()
                                ->color('gray'),
                            Infolists\Components\TextEntry::make('oldSalaryGrade.basic_salary')
                                ->label('Gaji Pokok Lama')
                                ->money('IDR'),
                        ])->columns(2),

                        Infolists\Components\Group::make([
                            Infolists\Components\TextEntry::make('newSalaryGrade.name')
                                ->label('Grade Baru')
                                ->badge()
                                ->color('success'),
                            Infolists\Components\TextEntry::make('newSalaryGrade.basic_salary')
                                ->label('Gaji Pokok Baru')
                                ->money('IDR'),
                        ])->columns(2),

                        Infolists\Components\Group::make([
                            Infolists\Components\TextEntry::make('salary_increase')
                                ->label('Kenaikan Gaji')
                                ->money('IDR')
                                ->getStateUsing(fn ($record) => $record->salary_increase)
                                ->badge()
                                ->color('success'),
                            Infolists\Components\TextEntry::make('salary_increase_percentage')
                                ->label('Persentase Kenaikan')
                                ->getStateUsing(fn ($record) => number_format($record->salary_increase_percentage, 2) . '%')
                                ->badge()
                                ->color('info'),
                        ])->columns(2),
                    ]),

                Infolists\Components\Section::make('Dokumen & Keterangan')
                    ->schema([
                        Infolists\Components\TextEntry::make('desc')
                            ->label('Deskripsi/Keterangan')
                            ->placeholder('Tidak ada keterangan'),
                        Infolists\Components\TextEntry::make('doc_promotion')
                            ->label('Dokumen Promosi')
                            ->getStateUsing(fn ($record) => $record->doc_promotion ? 'Dokumen tersedia' : 'Tidak ada dokumen')
                            ->badge()
                            ->color(fn ($record) => $record->doc_promotion ? 'success' : 'gray')
                            ->url(fn ($record) => $record->doc_promotion ? asset('storage/' . $record->doc_promotion) : null)
                            ->openUrlInNewTab(),
                    ]),

                Infolists\Components\Section::make('Informasi Sistem')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Dibuat Oleh'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d F Y H:i'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime('d F Y H:i'),
                    ])->columns(3)->collapsible(),
            ]);
    }
}
