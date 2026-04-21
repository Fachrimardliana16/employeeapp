<?php

namespace App\Filament\Employee\Resources\EmployeeResource\Pages;

use App\Filament\Employee\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Models\Employee;
use App\Models\MasterEmployeeStatusEmployment;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeResource\Widgets\EmployeeStatsOverview::class,
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Semua Pegawai')
                ->badge(Employee::query()->count()),
        ];

        // Ambil semua status kepegawaian yang aktif
        $statuses = MasterEmployeeStatusEmployment::where('is_active', true)->get();

        foreach ($statuses as $status) {
            $tabs[\Illuminate\Support\Str::slug($status->name)] = Tab::make($status->name)
                ->badge(Employee::query()->where('employment_status_id', $status->id)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('employment_status_id', $status->id));
        }

        return $tabs;
    }
}
