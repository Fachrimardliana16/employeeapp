<?php

namespace App\Filament\Employee\Resources\JobApplicationResource\Pages;

use App\Filament\Employee\Resources\JobApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;

use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListJobApplications extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = JobApplicationResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'baru' => Tab::make('Baru')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'submitted')),
            'review' => Tab::make('Proses Review')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'reviewed')),
            'interview' => Tab::make('Interview')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('status', ['interview_scheduled', 'interviewed'])),
            'diterima' => Tab::make('Diterima')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'accepted')),
            'ditolak_batal' => Tab::make('Ditolak/Batal')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('status', ['rejected', 'withdrawn'])),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            JobApplicationResource\Widgets\JobApplicationStatsOverview::class,
        ];
    }
}
