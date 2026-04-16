<?php

namespace App\Filament\Employee\Resources\InterviewProcessResource\Pages;

use App\Filament\Employee\Resources\InterviewProcessResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInterviewProcesses extends ListRecords
{
    protected static string $resource = InterviewProcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InterviewProcessResource\Widgets\InterviewProcessStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make('Semua'),
            'scheduled' => \Filament\Resources\Components\Tab::make('Terjadwal')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'scheduled'))
                ->icon('heroicon-m-calendar'),
            'passed' => \Filament\Resources\Components\Tab::make('Lulus')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('result', 'passed'))
                ->icon('heroicon-m-check-circle'),
            'failed' => \Filament\Resources\Components\Tab::make('Tidak Lulus')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('result', 'failed'))
                ->icon('heroicon-m-x-circle'),
            'pending' => \Filament\Resources\Components\Tab::make('Menunggu')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('result', 'pending'))
                ->icon('heroicon-m-clock'),
        ];
    }
}
