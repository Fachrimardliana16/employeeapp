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
use Illuminate\Support\Facades\Cache;

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
        // Cache tab data 10 menit — hitung count per status employment
        $tabData = Cache::remember('employee_tabs_data', now()->addMinutes(10), function () {
            $totalCount = Employee::query()->count();
            $statuses = MasterEmployeeStatusEmployment::where('is_active', true)->get();

            $statusCounts = Employee::query()
                ->selectRaw('employment_status_id, count(*) as total')
                ->groupBy('employment_status_id')
                ->pluck('total', 'employment_status_id');

            return compact('totalCount', 'statuses', 'statusCounts');
        });

        $tabs = [
            'all' => Tab::make('Semua Pegawai')
                ->badge($tabData['totalCount']),
        ];

        foreach ($tabData['statuses'] as $status) {
            $tabs[\Illuminate\Support\Str::slug($status->name)] = Tab::make($status->name)
                ->badge($tabData['statusCounts'][$status->id] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('employment_status_id', $status->id));
        }

        return $tabs;
    }
}
