<?php
namespace App\Filament\Employee\Resources\EmployeeAttendanceRecordResource\Widgets;

use App\Models\EmployeePermission;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ActivePermissionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Daftar Pegawai Izin & Cuti';
    protected int | string | array $columnSpan = 'full';
    protected static bool $isCollapsed = true;

    protected static string $view = 'filament.widgets.active-permissions-table-widget';

    public function getHeading(): ?string
    {
        return static::$heading;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                EmployeePermission::query()
                    ->where('approval_status', 'approved')
            )
            ->heading(null) // Heading is handled by the Section wrapper
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Pegawai')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('permission.name')
                    ->label('Jenis Izin/Cuti')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('start_permission_date')
                    ->label('Mulai')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('end_permission_date')
                    ->label('Sampai')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('permission_desc')
                    ->label('Keterangan')
                    ->limit(50),
            ])
            ->filters([
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\DatePicker::make('from')
                                    ->label('Dari Tanggal')
                                    ->default(now()),
                                \Filament\Forms\Components\DatePicker::make('until')
                                    ->label('Sampai Tanggal')
                                    ->default(now()),
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from = $data['from'] ?? now()->toDateString();
                        $until = $data['until'] ?? now()->toDateString();

                        return $query->where(function ($q) use ($from, $until) {
                            $q->whereDate('start_permission_date', '<=', $until)
                              ->whereDate('end_permission_date', '>=', $from);
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['from'] || ! $data['until']) {
                            return null;
                        }
                        return 'Rentang: ' . Carbon::parse($data['from'])->format('d/m/Y') . ' - ' . Carbon::parse($data['until'])->format('d/m/Y');
                    })
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->paginated(true)
            ->defaultPaginationPageOption(5);
    }
}
